<?php

namespace App\OrderGenerator;

use App\Component\ExchangePriceFormatter;
use App\Model\ExchangeOrder;
use App\Model\Symbol;
use App\Model\Trade;
use Doctrine\Common\Persistence\ObjectManager;

class LimitLadderBuyOrderGenerator extends AbstractOrderGenerator
{
    /** @var int */
    protected $ladderSize;

    /**
     * @param ExchangePriceFormatter $formatter
     * @param ObjectManager          $manager
     * @param int                    $ladderSize
     */
    public function __construct(
        ExchangePriceFormatter $formatter,
        ObjectManager $manager,
        int $ladderSize
    ) {
        parent::__construct($formatter, $manager);

        $this->ladderSize = $ladderSize;
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

    /**
     * {@inheritdoc}
     */
    protected function execute(Trade $trade, Symbol $validatedSymbol): array
    {
        $ladder = $this->calculateLadder(
            $this->getParameter($validatedSymbol, Symbol::LOT_SIZE, 'minQty'),
            $this->getParameter($validatedSymbol, Symbol::MIN_NOTIONAL, 'minNotional'),
            $this->formatter->getStepScale($validatedSymbol),
            $this->formatter->getPriceScale($validatedSymbol),
            $trade->getEntryLow(),
            $trade->getEntryHigh(),
            $trade->getQuantity()
        );

        $orders = [];
        foreach ($ladder as [$quantity, $price]) {
            $order = new ExchangeOrder();
            $order->setSymbol($validatedSymbol->getSymbol());
            $order->setSide(ExchangeOrder::BUY);
            $order->setPrice($price);
            $order->setQuantity($quantity);
            $order->setType(ExchangeOrder::LIMIT);
            $order->setTrade($trade);

            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * @param string $minQty      defines the minimum quantity allowed
     * @param string $minNotional defines the minimum notional value allowed for an order on a symbol
     * @param int    $stepScale
     * @param int    $priceScale
     * @param string $rangeLow
     * @param string $rangeHigh
     * @param string $quantity
     *
     * @return \Generator
     */
    protected function calculateLadder(
        string $minQty,
        string $minNotional,
        int $stepScale,
        int $priceScale,
        string $rangeLow,
        string $rangeHigh,
        string $quantity
    ): ?\Generator {
        $priceDifference = bcsub($rangeHigh, $rangeLow, $priceScale);
        $ladderSize = $this->ladderSize;

        do {
            $quantitySize = $ladderSize;
            --$ladderSize;

            if ($ladderSize === 0) {
                break;
            }

            $valuePerOrder = bcdiv($quantity, $quantitySize, $priceScale);
            $smallestQuantityPerOrder = bcdiv($valuePerOrder, $rangeHigh, $stepScale);
            $priceStep = (float) ($priceDifference / $ladderSize);

            // we need to convert it back to our local asset price for rounding differences due to scale limitations,
            // example 10 USD, divided by 8600 would be 0.001162 BTC rounded, but 0.001162 multiplied by 8600 USD would
            // be 9.99 USD, so if we don't, the MIN_NOMINAL filter will fail in some cases.
            //
            // the obvious choice here would be to use a bigger scale to accommodate for rounding difference, except
            // that would result in the trade actually taking more balance than available or was authorized.
            $recalculatedValuePerOrder = bcmul($smallestQuantityPerOrder, $rangeHigh, $priceScale);

            $this->logger->debug('Calculating suitable ladder set', [
                'value_per_order' => $valuePerOrder,
                'smallest_quantity_per_order' => $smallestQuantityPerOrder,
                'price_step' => $priceStep,
                'recalculated_value_per_order' => $recalculatedValuePerOrder,
                'ladder_size' => $ladderSize,
                'quantity_size' => $quantitySize,
            ]);
        } while (
            (
                bccomp($minQty, $smallestQuantityPerOrder, $stepScale) === 1 || // check for min. quantity rule
                bccomp($minNotional, $recalculatedValuePerOrder, $priceScale) === 1 // check for min. nominal rule
            )
            && $ladderSize > 0
        );

        if ($ladderSize > 0) {
            for ($n = 0; $n <= $ladderSize; ++$n) {
                $baseQuote = bcsub($rangeHigh, bcmul($priceStep, $n, $priceScale), $priceScale);
                $baseAsset = bcdiv($valuePerOrder, $baseQuote, $stepScale);

                yield [$baseAsset, $baseQuote];
            }
        } else {
            // unable to create a ladder within the lot rules, revert to single order
            yield [bcdiv($quantity, $rangeHigh, $stepScale), $rangeHigh];
        }
    }
}
