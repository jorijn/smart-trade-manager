<?php

namespace App\OrderGenerator;

use App\Model\Trade;

interface OrderGeneratorInterface
{
    /**
     * @param Trade $trade
     *
     * @return Order[]
     */
    public function generate(Trade $trade): array;

    /**
     * @param Trade $trade
     *
     * @return bool
     */
    public function supports(Trade $trade): bool;
}
