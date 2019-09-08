<?php

namespace App\Component;

use App\Exception\FilterNotFoundException;
use App\Exception\FilterParameterNotFoundException;
use App\Model\Symbol;
use App\Model\SymbolFilter;
use NumberFormatter;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class ExchangePriceFormatter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    /**
     * @param Symbol $symbol   the symbol which we're rounding for
     * @param string $quantity the quantity that should be rounded
     * @param bool   $market   indicated whether to use market buy setting
     *
     * @return string
     */
    public function roundStep(Symbol $symbol, string $quantity, $market = false): string
    {
        $desiredDecimals = $this->getStepScale($symbol, $market);
        $decimalIndex = strpos($quantity, '.');

        return substr($quantity, 0, $decimalIndex + $desiredDecimals + (int) ($desiredDecimals > 0));
    }

    /**
     * @param Symbol $symbol
     * @param bool   $market
     *
     * @return int
     */
    public function getStepScale(Symbol $symbol, bool $market = false): int
    {
        $filter = $this->getFilterFromSymbol($market ? Symbol::MARKET_LOT_SIZE : Symbol::LOT_SIZE, $symbol);
        $stepSize = $this->getParameterFromFilter($symbol, $filter, 'stepSize');

        return max((int) strpos($stepSize, '1') - 1, 0);
    }

    /**
     * @param string $filterType
     * @param Symbol $symbol
     *
     * @return SymbolFilter|null
     */
    protected function getFilterFromSymbol(string $filterType, Symbol $symbol): ?SymbolFilter
    {
        $filter = $symbol->getFilter($filterType);
        if (!$filter instanceof SymbolFilter) {
            $this->logger->warning('Filter {filter} requested for symbol {symbol} but was not found', [
                'symbol' => $symbol->getSymbol(),
                'filter' => $filterType,
            ]);

            throw new FilterNotFoundException(sprintf('filter with name %s was not found', $filterType));
        }

        return $filter;
    }

    /**
     * @param Symbol            $symbol
     * @param SymbolFilter|null $filter
     * @param string            $parameter
     *
     * @return mixed|null
     */
    protected function getParameterFromFilter(Symbol $symbol, SymbolFilter $filter, string $parameter)
    {
        $value = $filter->getParameter($parameter);
        if ($value === null) {
            $this->logger->warning('Parameter {parameter} requested on filter {filter} but was not found', [
                'symbol' => $symbol->getSymbol(),
                'parameter' => $parameter,
                'filter' => $filter->getFilterType(),
                'filter_id' => $filter->getId(),
            ]);

            throw new FilterParameterNotFoundException(
                sprintf('filter parameter `%s` was not found on symbol %s', $parameter, $symbol->getSymbol())
            );
        }

        return $value;
    }

    /**
     * @param Symbol $symbol
     * @param string $price
     *
     * @return string
     */
    public function roundTicks(Symbol $symbol, string $price): string
    {
        $precision = $this->getPriceScale($symbol);

        return (string) round((float) $price, $precision);
    }

    /**
     * @param Symbol $symbol
     *
     * @return int
     */
    public function getPriceScale(Symbol $symbol): int
    {
        $filter = $this->getFilterFromSymbol(Symbol::PRICE_FILTER, $symbol);
        $tickSize = $this->getParameterFromFilter($symbol, $filter, 'tickSize');

        $formatter = new NumberFormatter('en_US', NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, 0);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 8);

        return strlen(explode('.', $formatter->format($tickSize))[1]) ?? 0;
    }
}
