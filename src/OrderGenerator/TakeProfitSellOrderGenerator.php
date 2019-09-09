<?php

namespace App\OrderGenerator;

use App\Model\ExchangeOrder;
use App\Model\Symbol;
use App\Model\Trade;

class TakeProfitSellOrderGenerator extends AbstractOrderGenerator
{
    /**
     * @param Trade $trade
     *
     * @return bool
     */
    public function supports(Trade $trade): bool
    {
        return count($trade->getTakeProfits()) > 0 && $trade->getStoploss() === null;
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
        $alreadyAcquired = $this->calculateAlreadyAcquired($trade, $stepScale);
        $alreadySold = $this->calculateAlreadySold($trade, $stepScale);
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
        $orders = [];
        foreach ($sets as ['quantity' => $quantity, 'price' => $price, 'tp' => $takeProfit]) {
            $order = new ExchangeOrder();
            $order
                ->setSymbol($symbol->getSymbol())
                ->setSide('SELL')
                ->setType(ExchangeOrder::LIMIT)
                ->setQuantity($this->formatter->roundStep($symbol, $quantity))
                ->setPrice($this->formatter->roundTicks($symbol, $price))
                ->setTrade($trade)
                ->setTakeProfit($takeProfit);

            $orders[] = $order;
        }

        return $orders;
    }
}
