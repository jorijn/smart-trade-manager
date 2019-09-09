<?php

namespace App\OrderGenerator;

use App\Component\ExchangePriceFormatter;
use App\Model\ExchangeOrder;
use App\Model\Symbol;
use App\Model\Trade;
use Doctrine\Common\Persistence\ObjectManager;

class StopLimitLossOrderGenerator extends AbstractOrderGenerator
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
        return count($trade->getTakeProfits()) === 0 && $trade->getStoploss() !== null;
    }

    /**
     * @param Trade  $trade
     * @param Symbol $validatedSymbol
     *
     * @return Order[]
     */
    protected function execute(Trade $trade, Symbol $validatedSymbol): array
    {
        $riskPercentage = ($this->slRiskPercentage / 100) + 1;

        $stepScale = $this->formatter->getStepScale($validatedSymbol);
        $alreadyAcquired = $this->calculateAlreadyAcquired($trade, $stepScale);

        $order = new ExchangeOrder();
        $order
            ->setSide('SELL')
            ->setSymbol($validatedSymbol->getSymbol())
            ->setType(ExchangeOrder::STOP_LOSS_LIMIT)
            ->setQuantity($this->formatter->roundStep($validatedSymbol, $alreadyAcquired))
            ->setPrice($this->formatter->roundTicks(
                $validatedSymbol,
                bcdiv($trade->getStoploss(), (string) $riskPercentage, $this->formatter->getPriceScale($validatedSymbol))
            ))
            ->setStopPrice($this->formatter->roundTicks(
                $validatedSymbol,
                $trade->getStoploss()
            ))
            ->setTrade($trade);

        return [$order];
    }
}
