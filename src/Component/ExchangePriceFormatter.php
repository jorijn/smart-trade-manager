<?php

namespace App\Component;

use App\Exception\FilterNotFoundException;
use App\Exception\FilterParameterNotFoundException;
use App\Model\Symbol;
use App\Model\SymbolFilter;
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
        $filter = $this->getFilterFromSymbol($market ? Symbol::MARKET_LOT_SIZE : Symbol::LOT_SIZE, $symbol);
        $stepSize = $this->getParameterFromFilter($symbol, $filter, 'stepSize');

        $desiredDecimals = max((int) strpos($stepSize, '1') - 1, 0);
        $decimalIndex = strpos($quantity, '.');

        return substr($quantity, 0, $decimalIndex + $desiredDecimals + (int) ($desiredDecimals > 0));
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
            $this->logger->warning('filter requested for symbol but was not found', [
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
            $this->logger->warning('parameter requested on filter but was not found', [
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

    public function roundTicks(Symbol $symbol, string $price): string
    {
        // get tick size
    }
}
