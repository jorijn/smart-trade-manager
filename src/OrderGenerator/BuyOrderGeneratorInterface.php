<?php

namespace App\OrderGenerator;

use App\Model\Trade;

interface BuyOrderGeneratorInterface
{
    /**
     * @param Trade $trade
     *
     * @return Order[]
     */
    public function generate(Trade $trade): array;
}
