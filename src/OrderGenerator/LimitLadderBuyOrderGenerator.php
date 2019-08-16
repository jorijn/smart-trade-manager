<?php

namespace App\OrderGenerator;

use App\Model\Trade;

class LimitLadderBuyOrderGenerator implements BuyOrderGeneratorInterface
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Trade $trade): array
    {
    }
}
