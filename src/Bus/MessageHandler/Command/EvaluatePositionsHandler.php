<?php

namespace App\Bus\MessageHandler\Command;

use App\Bus\Message\Command\CreateExchangeOrdersCommand;
use App\Bus\Message\Command\EvaluatePositionsCommand;
use App\Bus\Message\Query\BuyOrderQuery;
use App\Bus\Message\Query\SellOrderQuery;
use App\Component\ExchangePriceFormatter;
use App\Model\ExchangeOrder;
use App\Model\Symbol;
use App\Model\Trade;
use App\Repository\ExchangeOrderRepository;
use App\Repository\SymbolRepository;
use App\Repository\TradeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EvaluatePositionsHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var CacheItemPoolInterface */
    protected $pool;
    /** @var ObjectManager */
    protected $manager;
    /** @var ExchangePriceFormatter */
    protected $formatter;
    /** @var HttpClientInterface */
    protected $binanceApiClient;
    /** @var TradeRepository|ObjectRepository */
    protected $tradeRepository;
    /** @var ExchangeOrderRepository|ObjectRepository */
    protected $orderRepository;
    /** @var SymbolRepository|ObjectRepository */
    protected $symbolRepository;
    /** @var MessageBusInterface */
    protected $queryBus;
    /** @var MessageBusInterface */
    protected $commandBus;

    /**
     * @param CacheItemPoolInterface $pool
     * @param LoggerInterface        $logger
     * @param ObjectManager          $manager
     * @param ExchangePriceFormatter $formatter
     * @param HttpClientInterface    $binanceApiClient
     * @param MessageBusInterface    $queryBus
     * @param MessageBusInterface    $commandBus
     */
    public function __construct(
        CacheItemPoolInterface $pool,
        LoggerInterface $logger,
        ObjectManager $manager,
        ExchangePriceFormatter $formatter,
        HttpClientInterface $binanceApiClient,
        MessageBusInterface $queryBus,
        MessageBusInterface $commandBus
    ) {
        $this->pool = $pool;
        $this->manager = $manager;
        $this->formatter = $formatter;
        $this->binanceApiClient = $binanceApiClient;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
        $this->setLogger($logger);

        $this->tradeRepository = $this->manager->getRepository(Trade::class);
        $this->orderRepository = $this->manager->getRepository(ExchangeOrder::class);
        $this->symbolRepository = $this->manager->getRepository(Symbol::class);
    }

    public function __invoke(EvaluatePositionsCommand $command)
    {
        $item = $this->pool->getItem(str_replace('\\', '_', get_class($command)));
        if ($item->isHit() && $item->get() !== $command->getKey()) {
            $this->logger->debug('evaluating positions: probably old command, newer will follow');

            return;
        }

        // fetch all pending trades from the database
        $trades = $this->tradeRepository->getPendingTrades();

        // iterate over trades; for n in y etc
        foreach ($trades as $trade) {
            $symbol = $this->symbolRepository->find($trade->getSymbol());
            $stepScale = $this->formatter->getStepScale($symbol);

            // calculate quantity already in our possession
            $buyQuantityFilled = $this->getBuyQuantityFilled($trade, $symbol, $stepScale);

            // determine type of type
            if (count($trade->getTakeProfits()) > 0) {
                // TODO how can we detect stop loss hit in oco's?

                //
            } elseif ($trade->getStoploss() !== null) {
                $slOrders = $this->orderRepository->findStopLossOrders($trade);
                $lostAmount = array_reduce(
                    $slOrders,
                    static function (string $lost, ExchangeOrder $order) use ($stepScale) {
                        return bcadd($lost, $order->getFilledQuantity(), $stepScale);
                    },
                    0.0
                );

                // did the stop loss order got hit somehow?
                if (bccomp('0', $lostAmount, $stepScale) === 1) {
                    $this->logger->info('stop loss hit, closing trade', ['trade' => $trade]);
                    $trade->setActive(false);
                    $this->manager->persist($trade);
                    $this->manager->flush();
                } else {
                    $activeSlOrders = array_filter($slOrders, static function (ExchangeOrder $order) {
                        return in_array($order->getStatus(), ['NEW', 'PARTIALLY_FILLED']);
                    });

                    $quantityProtectedInSL = array_reduce(
                        $activeSlOrders,
                        static function (string $protected, ExchangeOrder $order) use (
                            $stepScale
                        ) {
                            return bcadd($protected, $order->getQuantity(), $stepScale);
                        },
                        '0'
                    );

                    // did we acquire more quantity than is currently put in stop-loss order(s)?
                    if (bccomp($buyQuantityFilled, $quantityProtectedInSL, $stepScale) === 1) {
                        // cancel them
                        $this->cancelOrders(...$activeSlOrders);
                    }

                    // send out new stop-loss order(s)
                    $this->createSellOrders($trade);
                }
            }
        }
    }

    /**
     * @param Trade  $trade
     * @param Symbol $symbol
     * @param int    $stepScale
     *
     * @return string
     */
    protected function getBuyQuantityFilled(Trade $trade, Symbol $symbol, int $stepScale): string
    {
        return array_reduce(
            $this->orderRepository->findBuyOrders($trade),
            static function (string $quantity, ExchangeOrder $buy) use ($stepScale) {
                return bcadd($quantity, $buy->getFilledQuantity(), $stepScale);
            },
            '0'
        );
    }

    protected function cancelOrders(ExchangeOrder ...$orders): void
    {
        foreach ($orders as $order) {
            $result = $this->binanceApiClient->request('DELETE', 'v3/order', [
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
            }
        }
    }

    /**
     * @param Trade $trade
     */
    protected function createSellOrders(Trade $trade): void
    {
        $this->commandbus->dispatch(
            new CreateExchangeOrdersCommand(
                ...$this->handle(new SellOrderQuery($trade->getId()))
            )
        );
    }
}
