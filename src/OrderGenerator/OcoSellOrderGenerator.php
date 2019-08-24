<?php

namespace App\OrderGenerator;

use App\Component\ExchangePriceFormatter;
use App\Model\ExchangeOcoOrder;
use App\Model\ExchangeOrder;
use App\Model\Symbol;
use App\Model\TakeProfit;
use App\Model\Trade;
use App\Repository\ExchangeOrderRepository;
use App\Repository\SymbolRepository;
use App\Repository\TradeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

class OcoSellOrderGenerator extends AbstractOrderGenerator
{
    /** @var TradeRepository|ObjectRepository */
    protected $tradeRepository;
    /** @var ExchangeOrderRepository|ObjectRepository */
    protected $orderRepository;
    /** @var SymbolRepository|ObjectRepository */
    protected $symbolRepository;

    /**
     * @param ExchangePriceFormatter $formatter
     * @param ObjectManager          $manager
     */
    public function __construct(ExchangePriceFormatter $formatter, ObjectManager $manager)
    {
        parent::__construct($formatter, $manager);

        $this->tradeRepository = $this->manager->getRepository(Trade::class);
        $this->orderRepository = $this->manager->getRepository(ExchangeOrder::class);
        $this->symbolRepository = $this->manager->getRepository(Symbol::class);
    }

    /**
     * @param Trade $trade
     *
     * @return bool
     */
    public function supports(Trade $trade): bool
    {
        return count($trade->getTakeProfits()) > 0 && $trade->getStoploss() !== null;
    }

    /**
     * @param Trade  $trade
     * @param Symbol $validatedSymbol
     *
     * @return Order[]
     */
    protected function execute(Trade $trade, Symbol $validatedSymbol): array
    {
        $symbol = $this->symbolRepository->find($trade->getSymbol());
        $stepScale = $this->formatter->getStepScale($symbol);
        $alreadySold = $this->calculateAlreadySold($trade, $stepScale);
        $alreadyAcquired = $this->calculateAlreadyAcquired($trade, $stepScale);
        $leftToSell = bcsub($alreadyAcquired, $alreadySold, $stepScale);

        $sets = $this->calculatePossibleSellOrders($trade, $symbol, $stepScale, $alreadyAcquired, $leftToSell);

        return $this->convertSetsIntoOrders($trade, $sets, $symbol);
    }

    /**
     * @param Trade $trade
     * @param int   $stepScale
     *
     * @return string
     */
    protected function calculateAlreadySold(Trade $trade, int $stepScale): string
    {
        return array_reduce(
            $this->orderRepository->findTakeProfitOrders($trade),
            static function (string $sold, ExchangeOrder $order) use (
                $stepScale
            ) {
                return bcadd($sold, $order->getFilledQuantity(), $stepScale);
            },
            '0'
        );
    }

    /**
     * @param Trade $trade
     * @param int   $stepScale
     *
     * @return string
     */
    protected function calculateAlreadyAcquired(Trade $trade, int $stepScale): string
    {
        return array_reduce(
            $this->orderRepository->findBuyOrders($trade),
            static function (string $bought, ExchangeOrder $order) use (
                $stepScale
            ) {
                return bcadd($bought, $order->getFilledQuantity(), $stepScale);
            },
            '0'
        );
    }

    /**
     * @param Trade  $trade
     * @param object $symbol
     * @param int    $stepScale
     * @param string $alreadyAcquired
     * @param string $leftToSell
     *
     * @return array
     */
    protected function calculatePossibleSellOrders(
        Trade $trade,
        object $symbol,
        int $stepScale,
        string $alreadyAcquired,
        string $leftToSell
    ): array {
        $takeProfits = $trade->getTakeProfits()->toArray();
        $orders = [];

        // sort them on price descending, so the furthers tp point comes first
        usort($takeProfits, static function (TakeProfit $a, TakeProfit $b) {
            return $b->getPrice() <=> $a->getPrice();
        });

        /** @var TakeProfit $takeProfit */
        foreach ($takeProfits as $takeProfit) {
            if ($takeProfit->getPercentage() >= 100) {
                $size = $this->formatter->roundStep($symbol, $alreadyAcquired);
            }
            else {
                $size = bcmul(
                    $alreadyAcquired,
                    sprintf('0.%s', str_pad($takeProfit->getPercentage(), 2, STR_PAD_LEFT)),
                    $stepScale
                );
            }


            // if the desired chunk is less than what's left, override and break the loop
            if (bccomp($size, $leftToSell, $stepScale) === 1) {
                $orders[] = ['quantity' => $leftToSell, 'price' => $takeProfit->getPrice(), 'tp' => $takeProfit];
                break;
            }

            $orders[] = ['quantity' => $size, 'price' => $takeProfit->getPrice(), 'tp' => $takeProfit];
            $leftToSell = bcsub($leftToSell, $size, $stepScale);
        }

        return $orders;
    }

    /**
     * @param Trade  $trade
     * @param array  $sets
     * @param Symbol $symbol
     *
     * @return array
     */
    protected function convertSetsIntoOrders(Trade $trade, array $sets, Symbol $symbol): array
    {
        $orders = [];
        foreach ($sets as ['quantity' => $quantity, 'price' => $price, 'tp' => $takeProfit]) {
            $order = new ExchangeOcoOrder();
            $order
                ->setSymbol($symbol->getSymbol())
                ->setSide('SELL')
                ->setQuantity($this->formatter->roundStep($symbol, $quantity))
                ->setPrice($this->formatter->roundTicks($symbol, $price))
                ->setStopPrice($this->formatter->roundTicks($symbol, $trade->getStoploss()))
                // TODO make 1.01 configurable
                ->setStopLimitPrice($this->formatter->roundTicks(
                    $symbol,
                    bcdiv($trade->getStoploss(), '1.01', $this->formatter->getPriceScale($symbol))
                ))
                ->setTrade($trade)
                ->setTakeProfit($takeProfit);

            $orders[] = $order;
        }

        return $orders;
    }
}
