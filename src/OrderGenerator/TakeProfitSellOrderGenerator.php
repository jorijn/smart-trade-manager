<?php

namespace App\OrderGenerator;

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
        // TODO: Implement execute() method.
    }
}
