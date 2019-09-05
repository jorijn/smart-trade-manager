<?php

namespace App\Bus\MessageHandler\Command;

use App\Bus\Message\Command\SynchronizeOrderHistoryCommand;
use App\Event\OrderUpdatedEvent;
use App\Model\ExchangeOcoOrder;
use App\Model\ExchangeOrder;
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

class SynchronizeOrderHistoryHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ObjectManager */
    protected $manager;
    /** @var HttpClientInterface */
    protected $binanceApi;
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param LoggerInterface          $logger
     * @param ObjectManager            $manager
     * @param HttpClientInterface      $binanceApi
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        LoggerInterface $logger,
        ObjectManager $manager,
        HttpClientInterface $binanceApi,
        EventDispatcherInterface $dispatcher
    ) {
        $this->manager = $manager;
        $this->binanceApi = $binanceApi;
        $this->dispatcher = $dispatcher;

        $this->setLogger($logger);
    }

    /**
     * @param SynchronizeOrderHistoryCommand $command
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function __invoke(SynchronizeOrderHistoryCommand $command)
    {
        $this->processHistoryOfExchangeOrders($command);
        $this->processHistoryOfOcoExchangeOrders($command);
    }

    protected function processHistoryOfExchangeOrders(SynchronizeOrderHistoryCommand $command): void
    {
        $symbols = $this->manager->getRepository(ExchangeOrder::class)->getSymbolsWithPendingOrders();
        foreach ($symbols as $symbol) {
            $exchangeData = array_reduce(
                $this->getExchangeStatus($symbol),
                static function (array $orders, array $order) {
                    $orders[(string) $order['orderId']] = $order;

                    return $orders;
                },
                []
            );

            $ordersToBeUpdated = $this->manager
                ->getRepository(ExchangeOrder::class)
                ->findBy(['orderId' => array_keys($exchangeData)]);

            foreach ($ordersToBeUpdated as $order) {
                $data = $exchangeData[$order->getOrderId()];
                if ($data['updateTime'] <= $order->getUpdatedAt()) {
                    $this->logger->debug('received order info but local copy is more recent, ignoring', [
                        'id' => $order->getOrderId(),
                        'remote_ts' => $data['updateTime'],
                        'local_ts' => $order->getUpdatedAt(),
                    ]);
                    continue;
                }

                $order->setUpdatedAt($data['updateTime']);
                $order->setStatus($data['status']);
                $order->setFilledQuantity($data['executedQty'] ?? null);
                $order->setFilledQuoteQuantity($data['cummulativeQuoteQty'] ?? null);

                $this->manager->persist($order);
                $this->logger->info('updated order info from exchange', $data);

                if ($command->shouldTriggerEvents()) {
                    $this->dispatcher->dispatch(new OrderUpdatedEvent($order));
                }
            }

            $this->manager->flush();
        }
    }

    /**
     * @param array $pair
     *
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     *
     * @return array of orders
     */
    protected function getExchangeStatus(array $pair): array
    {
        ['symbol' => $symbol, 'oldest_order' => $oldestId] = $pair;
        $this->logger->debug(sprintf('symbol %s has pending orders, fetching status from exchange', $symbol), [
            'symbol' => $symbol,
            'oldest_order' => $oldestId,
        ]);

        $response = $this->binanceApi->request('GET', 'v3/allOrders', [
            'extra' => ['security_type' => 'USER_DATA'],
            'body' => [
                'symbol' => $symbol,
                'orderId' => $oldestId,
                'limit' => 1000,
            ],
        ])->toArray(false);

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    protected function processHistoryOfOcoExchangeOrders(): void
    {
        $pendingOcoOrders = $this->manager->getRepository(ExchangeOcoOrder::class)->getPendingOrders();

        foreach ($pendingOcoOrders as $order) {
            $response = $this->binanceApi->request('GET', 'v3/orderList', [
                'extra' => ['security_type' => 'USER_DATA'],
                'body' => [
                    'orderListId' => $order->getOrderListId(),
                ],
            ])->toArray(false);

            $order
                ->setListStatusType($response['listStatusType'])
                ->setListOrderStatus($response['listOrderStatus'])
                ->setUpdatedAt($response['transactionTime']);

            $this->logger->info('updated oco order info from exchange', $response);

            $this->manager->persist($order);
            $this->manager->flush();
        }
    }
}
