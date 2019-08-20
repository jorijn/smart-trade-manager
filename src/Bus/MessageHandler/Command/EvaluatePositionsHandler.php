<?php

namespace App\Bus\MessageHandler\Command;

use App\Bus\Message\Command\EvaluatePositionsCommand;
use App\Component\ExchangePriceFormatter;
use App\Model\ExchangeOrder;
use App\Model\StopLoss;
use App\Model\Symbol;
use App\Model\Trade;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
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

    /**
     * @param CacheItemPoolInterface $pool
     * @param LoggerInterface        $logger
     * @param ObjectManager          $manager
     * @param ExchangePriceFormatter $formatter
     * @param HttpClientInterface    $binanceApiClient
     */
    public function __construct(
        CacheItemPoolInterface $pool,
        LoggerInterface $logger,
        ObjectManager $manager,
        ExchangePriceFormatter $formatter,
        HttpClientInterface $binanceApiClient
    ) {
        $this->pool = $pool;
        $this->manager = $manager;
        $this->setLogger($logger);
        $this->formatter = $formatter;
        $this->binanceApiClient = $binanceApiClient;
    }

    public function __invoke(EvaluatePositionsCommand $command)
    {
        $item = $this->pool->getItem(str_replace('\\', '_', get_class($command)));
        if ($item->isHit() && $item->get() !== $command->getKey()) {
            $this->logger->debug('evaluating positions: probably old command, newer will follow');

            return;
        }

        $tradeRepository = $this->manager->getRepository(Trade::class);
        $orderRepository = $this->manager->getRepository(ExchangeOrder::class);
        $symbolRepository = $this->manager->getRepository(Symbol::class);

        // fetch all pending trades from the database
        $trades = $tradeRepository->getPendingTrades();

        // TODO subtract already sold profit of the quantity that's in posession,
        // if any TP was partially_filled, cancel all buys!

        // iterate over trades; for n in y etc
        foreach ($trades as $trade) {
            $symbol = $symbolRepository->find($trade->getSymbol());
            $stepScale = $this->formatter->getStepScale($symbol);

            // calculate quantity already in our possession
            $buyQuantityFilled = array_reduce(
                $orderRepository->findActiveBuyOrders($trade),
                static function (string $quantity, ExchangeOrder $buy) use ($stepScale) {
                    return bcadd($quantity, $buy->getFilledQuantity(), $stepScale);
                },
                '0'
            );

            // does this trade have TakeProfit objects?
            $takeProfits = $trade->getTakeProfits();
            if (count($takeProfits) > 0) {
                // do these TakeProfit objects have outstanding SELL orders?
                $tpSells = $orderRepository->findActiveSellOrdersByTakeProfits($takeProfits->toArray());
                $sellQuantityFilled = array_reduce(
                    $tpSells,
                    static function (string $quantity, ExchangeOrder $sell) use ($stepScale) {
                        return bcadd($quantity, $sell->getFilledQuantity(), $stepScale);
                    },
                    '0'
                );

                // is sum(filled) of buys higher than sum(quantity) of TP sells? -> we have acquired more!
                if (bccomp($buyQuantityFilled, $sellQuantityFilled, $stepScale) === 1) {
                    $this->cancelOrders(...$tpSells);
                }

                // create new SELL, TP or OCO orders because we have untracked quantity
                $this->createNewSellOrders($trade);
            } elseif ($trade->getStoploss() instanceof StopLoss) { // TODO implement!
                // does this trade have a stop loss object?

                // does this stop loss have outstanding sell order
                if (true) {
                    // if the bought quantity higher than the quantity of the outstanding stop loss order
                    if (true) {
                        // cancel the stop loss order
                        // [...]
                    }
                    // amount in SL is equal to what we own, leave it for now.
                }

                // place a new stop loss order
                // [...]
            }

            // nothing to do, iterate to the next trade
        }
    }

    // TODO find a better place for this
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
     * TODO: refactor to order generators.
     *
     * @param Trade $trade
     */
    protected function createNewSellOrders(Trade $trade)
    {
        $tradeRepository = $this->manager->getRepository(Trade::class);
        $orderRepository = $this->manager->getRepository(ExchangeOrder::class);
        $symbolRepository = $this->manager->getRepository(Symbol::class);

        $symbol = $symbolRepository->find($trade->getSymbol());
        $stepScale = $this->formatter->getStepScale($symbol);

        // calculate quantity already in our possession
        $acquiredQuantity = array_reduce(
            $orderRepository->findActiveBuyOrders($trade),
            static function (string $quantity, ExchangeOrder $buy) use ($stepScale) {
                return bcadd($quantity, $buy->getFilledQuantity(), $stepScale);
            },
            '0'
        );

        $takeProfits = $trade->getTakeProfits();
        $alreadySoldOrders = $orderRepository->findActiveSellOrdersByTakeProfits($takeProfits->toArray());
        $alreadySoldQuantity = array_reduce(
            $alreadySoldOrders,
            static function (string $quantity, ExchangeOrder $sell) use ($stepScale) {
                return bcadd($quantity, $sell->getFilledQuantity(), $stepScale);
            },
            '0'
        );

        $leftOverQuantity = bcsub($acquiredQuantity, $alreadySoldQuantity, $stepScale);

        // TODO divide left over quantity over take profit spots, or maybe just extract this logic to proper order generators
    }
}
