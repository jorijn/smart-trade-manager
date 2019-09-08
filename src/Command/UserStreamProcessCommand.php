<?php

namespace App\Command;

use Amp\Loop;
use Amp\Websocket;
use App\Bus\Message\Command\SynchronizeOrderHistoryCommand;
use App\Bus\Message\Event\WebsocketEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
    /** @var Websocket\Connection */
    protected $connection;
    /** @var MessageBusInterface */
    protected $eventBus;
    /** @var MessageBusInterface */
    protected $commandBus;

    /**
     * @param string                    $name
     * @param HttpClientInterface       $binanceApiClient
     * @param PropertyAccessorInterface $accessor
     * @param MessageBusInterface       $eventBus
     * @param MessageBusInterface       $commandBus
     * @param LoggerInterface           $logger
     */
    public function __construct(
        string $name,
        HttpClientInterface $binanceApiClient,
        PropertyAccessorInterface $accessor,
        MessageBusInterface $eventBus,
        MessageBusInterface $commandBus,
        LoggerInterface $logger
    ) {
        parent::__construct($name);
        $this->httpClient = $binanceApiClient;
        $this->accessor = $accessor;
        $this->eventBus = $eventBus;
        $this->commandBus = $commandBus;

        $this->setLogger($logger);
    }

    public function handleKillSignal(): void
    {
        $this->logger->info('Received kill signal');

        if ($this->connection) {
            // if we get here, close the websocket connection
            $this->logger->info('Closing websocket', ['listenKey' => $this->listenKey]);

            $this->connection->close();
            $this->httpClient->request(
                'DELETE',
                'v1/userDataStream',
                [
                    'extra' => ['security_type' => 'USER_STREAM'],
                    'body' => ['listenKey' => $this->listenKey],
                ]
            );
        }
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

        // start the loop
        $this->logger->info('Starting websocket', ['listenKey' => $this->listenKey]);

        try {
            $this->buildAndProcessWebsocket();
        } catch (Websocket\ClosedException $exception) {
            $this->logger->info('Closed websocket', [
                'reason' => $exception->getReason(),
            ]);
        }

        return 0;
    }

    protected function buildAndProcessWebsocket()
    {
        $lastPing = time();
        Loop::run(function () use (&$lastPing) {
            /* @var Websocket\Connection $connection */
            $this->connection = yield Websocket\connect('wss://stream.binance.com:9443/ws/'.$this->listenKey);
            $this->logger->info('Websocket connected', ['listenKey' => $this->listenKey]);

            /** @var Websocket\Message $message */
            while ($message = yield $this->connection->receive()) {
                pcntl_signal_dispatch();

                $payload = yield $message->buffer();
                $this->handlePayload($payload);

                // if the last ping was 30 minutes ago, send another.
                if ($lastPing < (time() - 1800)) {
                    $lastPing = time();

                    // this signals the API to keep the websocket open
                    $this->logger->info('Sending ping for websocket', ['listenKey' => $this->listenKey]);
                    $this->httpClient->request(
                        'PUT',
                        'v1/userDataStream',
                        [
                            'extra' => ['security_type' => 'USER_STREAM'],
                            'body' => ['listenKey' => $this->listenKey],
                        ]
                    );
                }
            }
        });
    }

    protected function handlePayload(string $payload): void
    {
        $data = json_decode($payload, true);
        $event = new WebsocketEvent($data['e'], $data);

        $this->logger->info('Dispatching websocket event', ['event' => $event]);

        $this->eventBus->dispatch($event);
    }
}
