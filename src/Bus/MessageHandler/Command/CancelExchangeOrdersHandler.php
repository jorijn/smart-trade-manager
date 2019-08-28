<?php

namespace App\Bus\MessageHandler\Command;

use App\Bus\Message\Command\CancelExchangeOrdersCommand;
use App\Event\OrderCancelledEvent;
use App\Model\ExchangeOrder;
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
        $this->logger->info('starting dispatching of order cancellation batch');

        /** @var ExchangeOrder $order */
        foreach ($command->getOrders() as $order) {
            $this->logger->info('dispatching order cancellation', ['order' => $order]);

            $result = $this->binanceApiClient->request('DELETE', $order->getEndpoint(), [
                'extra' => ['security_type' => 'TRADE'],
                'body' => [
                    'symbol' => $order->getSymbol(),
                    'orderId' => $order->getOrderId(),
                ],
            ]);

            $result = $result->toArray(false);

            // TODO maybe create a listener for this? -> extract logic
            if (isset($result['code'])) {
                $this->logger->error('failed to cancel order', [
                    'order' => $order,
                    'code' => $result['code'],
                    'reason' => $result['msg'],
                ]);

                continue;
            }

            /* @var ExchangeOrder $order */
            $order
                ->setFilledQuantity($result['executedQty'] ?? null)
                ->setFilledQuoteQuantity($result['cummulativeQuoteQty'] ?? null);

            if (isset($result['status'])) {
                $order->setStatus($result['status']);
            }

            $this->manager->persist($order);
            $this->manager->flush();

            $this->dispatcher->dispatch(new OrderCancelledEvent($order));
        }

        $this->logger->notice('end dispatching of new order batch');
    }
}
