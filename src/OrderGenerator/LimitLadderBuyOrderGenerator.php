<?php

namespace App\OrderGenerator;

use App\Component\ExchangePriceFormatter;
use App\Model\Trade;

class LimitLadderBuyOrderGenerator implements BuyOrderGeneratorInterface
{
    /** @var ExchangePriceFormatter */
    protected $formatter;

    /**
     * @param ExchangePriceFormatter $formatter
     */
    public function __construct(ExchangePriceFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Trade $trade): array
    {
    }

    /**
     * @param Trade $trade
     *
     * @return bool
     */
    public function supports(Trade $trade): bool
    {
        return $trade->getEntryLow() !== null && $trade->getEntryHigh() !== null;
    }
}
