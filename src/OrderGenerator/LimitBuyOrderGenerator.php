<?php

namespace App\OrderGenerator;

use App\Model\Trade;

class LimitBuyOrderGenerator implements BuyOrderGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(Trade $trade): array
    {
        // TODO: Implement generate() method.
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Trade $trade): bool
    {
        return $trade->getEntryHigh() === null && $trade->getEntryLow() !== null;
    }
}
