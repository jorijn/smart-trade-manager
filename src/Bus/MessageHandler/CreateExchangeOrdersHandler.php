<?php

namespace App\Bus\MessageHandler;

use App\Bus\Message\CreateExchangeOrdersCommand;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CreateExchangeOrdersHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /** @var HttpClientInterface */
    protected $binanceApiClient;

    /**
     * @param HttpClientInterface $binanceApiClient
     * @param LoggerInterface     $logger
     */
    public function __construct(HttpClientInterface $binanceApiClient, LoggerInterface $logger)
    {
        $this->binanceApiClient = $binanceApiClient;

        $this->setLogger($logger);
    }

    /**
     * @param CreateExchangeOrdersCommand $command
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function __invoke(CreateExchangeOrdersCommand $command)
    {
        $this->logger->notice('starting dispatching of new order batch');

        foreach ($command->getOrders() as $order) {
            $result = $this->binanceApiClient->request('POST', 'v3/order', [
                'extra' => ['security_type' => 'TRADE'],
                'body' => $order->toApiAttributes(),
            ]);

            // TODO save orders to database for further processing
            dump($result->getContent(false));
        }

        $this->logger->notice('end dispatching of new order batch');
    }
}
