<?php

namespace App\Bus\MessageHandler\Command;

use App\Bus\Message\Command\CreateExchangeOrdersCommand;
use App\Exception\BinanceApiException;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CreateExchangeOrdersHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var HttpClientInterface */
    protected $binanceApiClient;
    /** @var ObjectManager */
    protected $manager;
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param HttpClientInterface      $binanceApiClient
     * @param LoggerInterface          $logger
     * @param ObjectManager            $manager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        HttpClientInterface $binanceApiClient,
        LoggerInterface $logger,
        ObjectManager $manager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->binanceApiClient = $binanceApiClient;
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
        $this->setLogger($logger);
    }

    /**
     * @param CreateExchangeOrdersCommand $command
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function __invoke(CreateExchangeOrdersCommand $command)
    {
        $this->logger->info('Starting dispatching of new order batch');

        foreach ($command->getOrders() as $order) {
            $this->logger->info('Dispatching order');

            try {
                $result = $this->binanceApiClient->request('POST', $order->getEndpoint(), [
                    'extra' => ['security_type' => 'TRADE'],
                    'body' => $order->toApiAttributes(),
                ])->toArray(false);

                $order->update($result);
                $this->manager->persist($order);
            } catch (BinanceApiException $exception) {
                $this->logger->error('Failed to create order: {reason}', [
                    'code' => $exception->getCode(),
                    'reason' => $exception->getMessage(),
                    'request' => $order->toApiAttributes(),
                ]);
            }
        }

        $this->manager->flush();
        $this->logger->notice('End dispatching of new order batch');
    }
}
