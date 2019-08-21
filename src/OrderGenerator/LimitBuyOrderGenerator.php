<?php

namespace App\OrderGenerator;

use App\Model\ExchangeOrder;
use App\Model\Symbol;
use App\Model\Trade;

class LimitBuyOrderGenerator extends AbstractOrderGenerator
{
    /**
     * {@inheritdoc}
     */
    public function supports(Trade $trade): bool
    {
        return $trade->getEntryHigh() === null && $trade->getEntryLow() !== null;
    }

    /**
     * @param Trade  $trade
     * @param Symbol $validatedSymbol
     *
     * @return Order[]
     */
    protected function execute(Trade $trade, Symbol $validatedSymbol): array
    {
        $entry = $trade->getEntryLow();

        $order = new ExchangeOrder();
        $order->setSymbol($validatedSymbol->getSymbol());
        $order->setSide(ExchangeOrder::BUY);
        $order->setPrice($this->formatter->roundTicks($validatedSymbol, $entry));
        $order->setType(ExchangeOrder::LIMIT);
        $order->setTrade($trade);
        $order->setQuantity($this->formatter->roundStep(
            $validatedSymbol,
            bcdiv($trade->getQuantity(), $entry, $this->formatter->getStepScale($validatedSymbol))
        ));

        return [$order];
    }
}
