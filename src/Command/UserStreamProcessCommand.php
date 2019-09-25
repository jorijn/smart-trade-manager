<?php

namespace App\Command;

use App\Bus\Message\Command\SynchronizeOrderHistoryCommand;
use App\Bus\Message\Event\WebsocketEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\LoopInterface;
use React\Socket\Connector as ReactConnector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class UserStreamProcessCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected static $defaultName = 'app:user-stream:process';
    /** @var HttpClientInterface */
    protected $httpClient;
    /** @var PropertyAccessorInterface */
    protected $accessor;
    /** @var string */
    protected $listenKey;
    /** @var WebSocket */
    protected $websocket;
    /** @var MessageBusInterface */
    protected $eventBus;
    /** @var MessageBusInterface */
    protected $commandBus;
    /** @var LoopInterface */
    protected $loop;
    /** @var string */
    protected $dns;
    /** @var int */
    protected $timeout;

    /**
     * @param string                    $name
     * @param HttpClientInterface       $binanceApiClient
     * @param PropertyAccessorInterface $accessor
     * @param MessageBusInterface       $eventBus
     * @param MessageBusInterface       $commandBus
     * @param LoggerInterface           $logger
     * @param LoopInterface             $loop
     * @param string                    $dns
     * @param int                       $timeout
     */
    public function __construct(
        string $name,
        HttpClientInterface $binanceApiClient,
        PropertyAccessorInterface $accessor,
        MessageBusInterface $eventBus,
        MessageBusInterface $commandBus,
        LoggerInterface $logger,
        LoopInterface $loop,
        string $dns = '8.8.8.8',
        int $timeout = 10
    ) {
        parent::__construct($name);
        $this->httpClient = $binanceApiClient;
        $this->accessor = $accessor;
        $this->eventBus = $eventBus;
        $this->commandBus = $commandBus;
        $this->loop = $loop;
        $this->dns = $dns;
        $this->timeout = $timeout;

        $this->setLogger($logger);
    }

    public function handleKillSignal(): void
    {
        $this->logger->info('Received kill signal');

        if ($this->websocket) {
            // if we get here, close the websocket connection
            $this->logger->info('Closing websocket', ['listenKey' => $this->listenKey]);

            $this->websocket->close();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addOption(
            'time-limit',
            null,
            InputOption::VALUE_REQUIRED,
            'The time limit in seconds the worker can run'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // set the signal handler so we can close the websocket when the process is killed
        pcntl_signal(SIGINT, [$this, 'handleKillSignal']);
        pcntl_signal(SIGTERM, [$this, 'handleKillSignal']);

        // sync before starting
        $this->commandBus->dispatch(new SynchronizeOrderHistoryCommand());

        // get listener key to connect to websocket
        $response = $this->httpClient->request(
            'POST',
            'v1/userDataStream',
            ['extra' => ['security_type' => 'USER_STREAM']]
        );

        $this->listenKey = $this->accessor->getValue($response->toArray(false), '[listenKey]');

        $timeLimit = $input->getOption('time-limit');
        if ($timeLimit) {
            $this->loop->addTimer((int) $timeLimit, function () use ($timeLimit) {
                $this->websocket->close(1000, sprintf('reached time limit of %d seconds', $timeLimit));
            });
        }

        $this->startLoop();
    }

    protected function startLoop()
    {
        $reactConnector = new ReactConnector($this->loop, [
            'dns' => $this->dns,
            'timeout' => $this->timeout,
        ]);

        $connector = new Connector($this->loop, $reactConnector);

        $this->logger->info('Starting websocket', ['listenKey' => $this->listenKey]);
        $connector('wss://stream.binance.com:9443/ws/'.$this->listenKey, [], [])
            ->then(function (WebSocket $webSocket) {
                $this->websocket = $webSocket;
                $this->logger->info('Websocket connected', ['listenKey' => $this->listenKey]);

                $webSocket->on('message', function (MessageInterface $message) {
                    $this->onWebsocketPayload((string) $message);
                });

                $webSocket->on('close', function ($code = null, $reason = null) {
                    $this->onWebsocketClose($code, $reason);
                });
            })
            ->otherwise(function (Throwable $exception) {
                $this->logger->error('Websocket could not connect: {reason}', [
                    'exception' => $exception,
                    'reason' => $exception->getMessage() ?: get_class($exception),
                ]);
            });

        $this->registerKeyRenewalTimer();
        $this->registerSignalDispatcher();

        $this->loop->run();
    }

    protected function onWebsocketPayload(string $payload): void
    {
        $data = json_decode($payload, true);
        $event = new WebsocketEvent($data['e'], $data);

        $this->logger->info('Dispatching websocket event', ['event' => $data['e'], 'payload' => $data]);

        $this->eventBus->dispatch($event);
    }

    /**
     * @param mixed $code
     * @param mixed $reason
     *
     * @throws TransportExceptionInterface
     */
    protected function onWebsocketClose($code, $reason)
    {
        $this->httpClient->request(
            'DELETE',
            'v1/userDataStream',
            [
                'extra' => ['security_type' => 'USER_STREAM'],
                'body' => ['listenKey' => $this->listenKey],
            ]
        );

        $this->logger->info(
            'Websocket closed',
            ['listenKey' => $this->listenKey, 'code' => $code, 'reason' => $reason]
        );

        $this->loop->stop();
    }

    protected function registerKeyRenewalTimer(): void
    {
        $this->loop->addPeriodicTimer(1800.0, function () {
            $this->logger->info('Sending ping for websocket', ['listenKey' => $this->listenKey]);
            $this->httpClient->request(
                'PUT',
                'v1/userDataStream',
                [
                    'extra' => ['security_type' => 'USER_STREAM'],
                    'body' => ['listenKey' => $this->listenKey],
                ]
            );
        });
    }

    protected function registerSignalDispatcher(): void
    {
        $this->loop->addPeriodicTimer(1, static function () {
            pcntl_signal_dispatch();
        });
    }
}
