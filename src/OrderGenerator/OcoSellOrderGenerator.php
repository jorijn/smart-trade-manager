<?php

namespace App\OrderGenerator;

use App\Component\ExchangePriceFormatter;
use App\Model\ExchangeOcoOrder;
use App\Model\Symbol;
use App\Model\Trade;
use Doctrine\Common\Persistence\ObjectManager;

class OcoSellOrderGenerator extends AbstractOrderGenerator
{
    /** @var float */
    protected $slRiskPercentage;

    /**
     * @param ExchangePriceFormatter $formatter
     * @param ObjectManager          $manager
     * @param float                  $slRiskPercentage
     */
    public function __construct(ExchangePriceFormatter $formatter, ObjectManager $manager, float $slRiskPercentage)
    {
        parent::__construct($formatter, $manager);

        $this->slRiskPercentage = $slRiskPercentage;
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
        $stepScale = $this->formatter->getStepScale($validatedSymbol);
        $alreadySold = $this->calculateAlreadySold($trade, $stepScale);
        $alreadyAcquired = $this->calculateAlreadyAcquired($trade, $stepScale);
        $leftToSell = bcsub($alreadyAcquired, $alreadySold, $stepScale);

        $sets = $this->calculatePossibleSellOrders($trade, $validatedSymbol, $stepScale, $alreadyAcquired, $leftToSell);

        return $this->convertSetsIntoOrders($trade, $sets, $validatedSymbol);
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
        $riskPercentage = ($this->slRiskPercentage / 100) + 1;

        $orders = [];
        foreach ($sets as ['quantity' => $quantity, 'price' => $price, 'tp' => $takeProfit]) {
            $order = new ExchangeOcoOrder();
            $order
                ->setSymbol($symbol->getSymbol())
                ->setSide('SELL')
                ->setQuantity($this->formatter->roundStep($symbol, $quantity))
                ->setPrice($this->formatter->roundTicks($symbol, $price))
                ->setStopPrice($this->formatter->roundTicks($symbol, $trade->getStoploss()))
                ->setStopLimitPrice($this->formatter->roundTicks(
                    $symbol,
                    bcdiv($trade->getStoploss(), (string) $riskPercentage, $this->formatter->getPriceScale($symbol))
                ))
                ->setTrade($trade)
                ->setTakeProfit($takeProfit);

            $orders[] = $order;
        }

        return $orders;
    }
}
