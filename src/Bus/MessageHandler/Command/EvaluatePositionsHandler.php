<?php

namespace App\Bus\MessageHandler\Command;

use App\Bus\Message\Command\CancelExchangeOrdersCommand;
use App\Bus\Message\Command\CreateExchangeOrdersCommand;
use App\Bus\Message\Command\EvaluatePositionsCommand;
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
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class EvaluatePositionsHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use HandleTrait;

    /** @var CacheItemPoolInterface */
    protected $pool;
    /** @var ObjectManager */
    protected $manager;
    /** @var ExchangePriceFormatter */
    protected $formatter;
    /** @var TradeRepository|ObjectRepository */
    protected $tradeRepository;
    /** @var ExchangeOrderRepository|ObjectRepository */
    protected $orderRepository;
    /** @var SymbolRepository|ObjectRepository */
    protected $symbolRepository;
    /** @var MessageBusInterface */
    protected $commandBus;

    /**
     * @param CacheItemPoolInterface $pool
     * @param LoggerInterface        $logger
     * @param ObjectManager          $manager
     * @param ExchangePriceFormatter $formatter
     * @param MessageBusInterface    $queryBus
     * @param MessageBusInterface    $commandBus
     */
    public function __construct(
        CacheItemPoolInterface $pool,
        LoggerInterface $logger,
        ObjectManager $manager,
        ExchangePriceFormatter $formatter,
        MessageBusInterface $queryBus,
        MessageBusInterface $commandBus
    ) {
        $this->pool = $pool;
        $this->manager = $manager;
        $this->formatter = $formatter;
        $this->messageBus = $queryBus;
        $this->commandBus = $commandBus;
        $this->setLogger($logger);

        $this->tradeRepository = $this->manager->getRepository(Trade::class);
        $this->orderRepository = $this->manager->getRepository(ExchangeOrder::class);
        $this->symbolRepository = $this->manager->getRepository(Symbol::class);
    }

    public function __invoke(EvaluatePositionsCommand $command)
    {
        $item = $this->pool->getItem(str_replace('\\', '_', get_class($command)));
        if (!$item->isHit() || $item->get() !== $command->getKey()) {
            $this->logger->debug('evaluating positions: probably old command, newer will follow');

            return;
        }

        // fetch all pending trades from the database
        $trades = $this->tradeRepository->getPendingTrades();

        // iterate over trades; for n in y etc
        foreach ($trades as $trade) {
            $this->logger->debug('evaluating positions: evaluating trade', ['trade' => $trade]);
            $symbol = $this->symbolRepository->find($trade->getSymbol());
            $stepScale = $this->formatter->getStepScale($symbol);

            // calculate quantity already in our possession
            $buyQuantityFilled = $this->getBuyQuantityFilled($trade, $symbol, $stepScale);
            $this->logger->debug(
                'evaluating positions: buy quantity fetched',
                ['buy_quantity_filled' => $buyQuantityFilled, 'trade_id' => $trade->getId()]
            );

            // only continue processing when we have something in our possession
            if (bccomp($buyQuantityFilled, '0', $stepScale) === 0) {
                $this->logger->debug(
                    'evaluating positions: stop processing, nothing in procession',
                    ['buy_quantity_filled' => $buyQuantityFilled, 'trade_id' => $trade->getId()]
                );
                continue;
            }

            // determine type of type
            if (count($trade->getTakeProfits()) > 0) {
                $this->logger->debug(
                    'evaluating positions: entering take profit mode',
                    ['trade_id' => $trade->getId()]
                );
                $exchangeOrders = $this->orderRepository->findTakeProfitOrders($trade);

                // these calculations are only needed when there are / were sell orders
                if (count($exchangeOrders) > 0) {
                    $this->logger->debug('evaluating positions: found exchange selling orders', [
                        'ids' => array_map(function (ExchangeOrder $order) {
                            return $order->getOrderId();
                        }, $exchangeOrders),
                        'trade_id' => $trade->getId(),
                    ]);

                    $leftToSellInOrder = array_reduce(
                        $exchangeOrders,
                        static function (string $left, ExchangeOrder $order) use (
                            $stepScale
                        ) {
                            if ($order->getStatus() === 'CANCELLED') {
                                return $left;
                            }

                            // subtract what is sold of the total, this is the remainder of what to sell
                            return bcadd(
                                $left,
                                bcsub($order->getQuantity(), $order->getFilledQuantity(), $stepScale),
                                $stepScale
                            );
                        },
                        '0'
                    );

                    $this->logger->debug(
                        'evaluating positions: calculated what\'s left to sell',
                        ['left_to_sell_in_order' => $leftToSellInOrder, 'trade_id' => $trade->getId()]
                    );

                    // subtract everything that's already sold for this trade from our trading stack, this is the remainder
                    // of what is left to sell
                    $leftToSellInPossession = array_reduce(
                        $exchangeOrders,
                        static function (string $total, ExchangeOrder $order) use (
                            $stepScale
                        ) {
                            return bcsub($total, $order->getFilledQuantity(), $stepScale);
                        },
                        $buyQuantityFilled
                    );

                    $this->logger->debug(
                        'evaluating positions: calculated what\'s left to sell in pocession',
                        ['left_to_sell_in_pocession' => $leftToSellInPossession, 'trade_id' => $trade->getId()]
                    );

                    $alreadySoldForTrade = array_reduce(
                        $exchangeOrders,
                        static function (string $sold, ExchangeOrder $order) use (
                            $stepScale
                        ) {
                            return bcadd($sold, $order->getFilledQuantity(), $stepScale);
                        },
                        '0'
                    );

                    $this->logger->debug(
                        'evaluating positions: calculated what\'s already sold for trade',
                        ['already_sold_for_trade' => $alreadySoldForTrade, 'trade_id' => $trade->getId()]
                    );

                    // did we already took some profit or theoretically hit stop-loss? -> cancel all outstanding buy orders
                    if (bccomp('0', $alreadySoldForTrade, $stepScale) !== 0) {
                        $this->logger->debug(
                            'evaluating positions: cancelling orders, some target (tp or sl) got hit',
                            ['trade_id' => $trade->getId()]
                        );

                        $this->cancelOrders(...$this->orderRepository->findBuyOrders($trade));
                    }

                    // did we acquire more than currently is reserved in sell order(s)? also check if the bought X% more
                    // than currently is being sold, this to incorporate a margin for dust
                    if (bccomp($leftToSellInPossession, $leftToSellInOrder, $stepScale) === 1) {
                        $percentage = null;
                        if (bccomp($leftToSellInOrder, '0', $stepScale) === 1) {
                            $percentage = bcdiv($leftToSellInPossession, $leftToSellInOrder, $stepScale);
                            $percentage = bcsub($percentage, '1', $stepScale);
                        }

                        $logContext = [
                            'left_to_sell_in_possession' => $leftToSellInPossession,
                            'left_to_sell_in_order' => $leftToSellInOrder,
                            'trade_id' => $trade->getId(),
                            'percentage' => $percentage,
                        ];

                        if ($percentage === null || bccomp($percentage, '0.0001', $stepScale) === 1) {
                            $this->logger->debug(
                                'evaluating positions: acquired more than currently is being sold',
                                $logContext
                            );

                            $this->cancelOrders(...$exchangeOrders);
                            $this->createSellOrders($trade);
                        } else {
                            $this->logger->debug(
                                'evaluating positions: acquired more but difference is within 0.01% margin',
                                $logContext
                            );
                        }
                    }
                } else {
                    // place new orders
                    $this->createSellOrders($trade);
                }
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
                if (bccomp($lostAmount, '0', $stepScale) === 1) {
                    $this->logger->info('stop loss hit, closing trade', ['trade_id' => $trade->getId()]);
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
                    // -> send out new stop-loss order(s)
                    if (bccomp($buyQuantityFilled, $quantityProtectedInSL, $stepScale) === 1) {
                        if (count($activeSlOrders) > 0) {
                            // cancel them
                            $this->cancelOrders(...$activeSlOrders);
                        }

                        $this->createSellOrders($trade);
                    }
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

    /**
     * @param ExchangeOrder ...$orders
     */
    protected function cancelOrders(ExchangeOrder ...$orders): void
    {
        $this->logger->debug(
            'evaluating positions: about to cancel orders',
            [
                'order_ids' => array_map(static function (ExchangeOrder $order) {
                    return $order->getOrderId();
                }, $orders),
            ]
        );

        $orders = array_filter($orders, static function (ExchangeOrder $order) {
            return $order->getStatus() === 'NEW' || $order->getStatus() === 'PARTIALLY_FILLED';
        });

        if (count($orders) > 0) {
            $this->logger->debug(
                'evaluating positions: cancelling orders',
                [
                    'order_ids' => array_map(static function (ExchangeOrder $order) {
                        return $order->getOrderId();
                    }, $orders),
                ]
            );

            $this->commandBus->dispatch(new CancelExchangeOrdersCommand(...$orders));
        }
    }

    /**
     * @param Trade $trade
     */
    protected function createSellOrders(Trade $trade): void
    {
        $this->logger->debug('evaluating positions: creating new sell orders');

        $this->commandBus->dispatch(
            new CreateExchangeOrdersCommand(
                ...$this->handle(new SellOrderQuery($trade->getId()))
            )
        );
    }
}
