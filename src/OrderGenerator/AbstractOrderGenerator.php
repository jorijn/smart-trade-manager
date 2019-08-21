<?php

namespace App\OrderGenerator;

use App\Component\ExchangePriceFormatter;
use App\Exception\FilterNotFoundException;
use App\Exception\SymbolNotFoundException;
use App\Model\Symbol;
use App\Model\SymbolFilter;
use App\Model\Trade;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

abstract class AbstractOrderGenerator implements OrderGeneratorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ExchangePriceFormatter */
    protected $formatter;
    /** @var ObjectManager */
    protected $manager;

    /**
     * @param ExchangePriceFormatter $formatter
     * @param ObjectManager          $manager
     */
    public function __construct(ExchangePriceFormatter $formatter, ObjectManager $manager)
    {
        $this->formatter = $formatter;
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Trade $trade): array
    {
        // easy container method for getting symbol
        $symbol = $this->manager->find(Symbol::class, $trade->getSymbol());
        if (!$symbol instanceof Symbol) {
            $this->logger->error('requested symbol was not found', ['symbol' => $trade->getSymbol()]);

            throw new SymbolNotFoundException('requested symbol was not found');
        }

        return $this->execute($trade, $symbol);
    }

    /**
     * @param Trade  $trade
     * @param Symbol $validatedSymbol
     *
     * @return Order[]
     */
    abstract protected function execute(Trade $trade, Symbol $validatedSymbol): array;

    /**
     * @param Symbol $symbol
     * @param string $filterType
     * @param string $parameter
     *
     * @return mixed
     */
    protected function getParameter(Symbol $symbol, string $filterType, string $parameter)
    {
        $filter = $symbol->getFilter($filterType);
        if (!$filter instanceof SymbolFilter) {
            $warning = sprintf(
                'filter %s not found on symbol %s',
                $filterType,
                $symbol->getSymbol()
            );

            $this->logger->warning($warning, ['symbol' => $symbol, 'filter' => $filterType, 'parameter' => $parameter]);

            throw new FilterNotFoundException($warning);
        }

        $parameter = $filter->getParameter($parameter);
        if ($parameter === null) {
            $warning = sprintf(
                'parameter %s not found on filter %s on symbol %s',
                $parameter,
                $filterType,
                $symbol->getSymbol()
            );

            $this->logger->warning($warning, ['symbol' => $symbol, 'filter' => $filterType, 'parameter' => $parameter]);

            throw new ParameterNotFoundException($warning);
        }

        return $parameter;
    }
}
