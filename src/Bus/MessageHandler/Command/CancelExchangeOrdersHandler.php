<?php

namespace App\Bus\MessageHandler\Command;

use App\Bus\Message\Command\CancelExchangeOrdersCommand;
use App\Exception\BinanceApiException;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CancelExchangeOrdersHandler implements LoggerAwareInterface
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

    public function __invoke(CancelExchangeOrdersCommand $command)
    {
        $this->logger->info('Starting dispatching of order cancellation batch');

        foreach ($command->getOrders() as $order) {
            $logContext = [$order->getAttributeIdentifier() => $order->getAttributeIdentifierValue()];
            $this->logger->info('Dispatching order cancellation', $logContext);

            try {
                $result = $this->binanceApiClient->request('DELETE', $order->getEndpoint(), [
                    'extra' => ['security_type' => 'TRADE'],
                    'body' => [
                        'symbol' => $order->getSymbol(),
                        $order->getAttributeIdentifier() => $order->getAttributeIdentifierValue(),
                    ],
                ])->toArray(false);

                $order->update($result);
                $this->manager->persist($order);
            } catch (BinanceApiException $exception) {
                $this->logger->error(
                    'Failed to cancel order: {reason}',
                    $logContext + [
                        'code' => $exception->getCode(),
                        'reason' => $exception->getMessage(),
                    ]
                );
            }
        }

        $this->manager->flush();
        $this->logger->notice('End dispatching of new order batch');
    }
}
