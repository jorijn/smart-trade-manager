<?php

namespace App\Bus\MessageHandler\Command;

use App\Bus\Message\Command\CreateExchangeOrdersCommand;
use App\Event\OrderCreatedEvent;
use App\Exception\BinanceApiException;
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
        $this->logger->info('starting dispatching of new order batch');

        foreach ($command->getOrders() as $order) {
            $this->logger->info('dispatching order', ['order' => $order]);

            try {
                $result = $this->binanceApiClient->request('POST', $order->getEndpoint(), [
                    'extra' => ['security_type' => 'TRADE'],
                    'body' => $order->toApiAttributes(),
                ])->toArray(false);
            } catch (BinanceApiException $exception) {
                $this->logger->error('failed to create order', [
                    'order' => $order,
                    'code' => $exception->getCode(),
                    'reason' => $exception->getMessage(),
                ]);
            }

            switch (get_class($order)) {
                case ExchangeOrder::class:
                    /* @var ExchangeOrder $order */
                    $order
                        ->setOrderId($result['orderId'])
                        ->setUpdatedAt($result['transactTime'])
                        ->setStatus($result['status'])
                        ->setQuantity($result['origQty'])
                        ->setFilledQuantity($result['executedQty'] ?? null)
                        ->setFilledQuoteQuantity($result['cummulativeQuoteQty'] ?? null);

                    $this->manager->persist($order);
                    $this->manager->flush();

                    $this->dispatcher->dispatch(new OrderCreatedEvent($order));
                    break;
                case ExchangeOcoOrder::class:
                    /* @var ExchangeOcoOrder $order */
                    $order
                        ->setOrderListId($result['orderListId'])
                        ->setListStatusType($result['listStatusType'])
                        ->setListOrderStatus($result['listOrderStatus'])
                        ->setUpdatedAt($result['transactionTime']);

                    $this->manager->persist($order);
                    $this->manager->flush();

                    foreach ($result['orderReports'] ?? [] as $orderReport) {
                        $subOrder = new ExchangeOrder();
                        $subOrder
                            ->setSymbol($orderReport['symbol'])
                            ->setOrderId($orderReport['orderId'])
                            ->setOrderList($order)
                            ->setPrice($orderReport['price'])
                            ->setUpdatedAt($orderReport['transactTime'])
                            ->setStatus($orderReport['status'])
                            ->setQuantity($orderReport['origQty'])
                            ->setTimeInForce($orderReport['timeInForce'])
                            ->setType($orderReport['type'])
                            ->setSide($orderReport['side'])
                            ->setStopPrice($orderReport['stopPrice'] ?? null)
                            ->setFilledQuantity($orderReport['executedQty'] ?? null)
                            ->setFilledQuoteQuantity($orderReport['cummulativeQuoteQty'] ?? null)
                            ->setTrade($order->getTrade())
                            ->setTakeProfit($order->getTakeProfit());

                        $this->manager->persist($subOrder);
                        $this->manager->flush();

                        $this->dispatcher->dispatch(new OrderCreatedEvent($subOrder));
                    }
                    break;
                default:
                    throw new \InvalidArgumentException('unknown type '.get_class($order).' received');
            }
        }

        $this->logger->notice('end dispatching of new order batch');
    }
}
