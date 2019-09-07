<?php

namespace App\OrderGenerator;

use App\Component\ExchangePriceFormatter;
use App\Exception\FilterNotFoundException;
use App\Exception\SymbolNotFoundException;
use App\Model\ExchangeOrder;
use App\Model\Symbol;
use App\Model\SymbolFilter;
use App\Model\TakeProfit;
use App\Model\Trade;
use App\Repository\ExchangeOrderRepository;
use App\Repository\SymbolRepository;
use App\Repository\TradeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
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
    /** @var TradeRepository|ObjectRepository */
    protected $tradeRepository;
    /** @var ExchangeOrderRepository|ObjectRepository */
    protected $orderRepository;
    /** @var SymbolRepository|ObjectRepository */
    protected $symbolRepository;

    /**
     * @param ExchangePriceFormatter $formatter
     * @param ObjectManager          $manager
     */
    public function __construct(ExchangePriceFormatter $formatter, ObjectManager $manager)
    {
        $this->formatter = $formatter;
        $this->manager = $manager;

        $this->tradeRepository = $this->manager->getRepository(Trade::class);
        $this->orderRepository = $this->manager->getRepository(ExchangeOrder::class);
        $this->symbolRepository = $this->manager->getRepository(Symbol::class);
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

    /**
     * @param Trade $trade
     * @param int   $stepScale
     *
     * @return string
     */
    protected function calculateAlreadyAcquired(Trade $trade, int $stepScale): string
    {
        return array_reduce(
            $this->orderRepository->findBuyOrders($trade),
            static function (string $bought, ExchangeOrder $order) use (
                $stepScale
            ) {
                return bcadd($bought, $order->getFilledQuantity(), $stepScale);
            },
            '0'
        );
    }

    /**
     * @param Trade $trade
     * @param int   $stepScale
     *
     * @return string
     */
    protected function calculateAlreadySold(Trade $trade, int $stepScale): string
    {
        return array_reduce(
            $this->orderRepository->findTakeProfitOrders($trade),
            static function (string $sold, ExchangeOrder $order) use (
                $stepScale
            ) {
                return bcadd($sold, $order->getFilledQuantity(), $stepScale);
            },
            '0'
        );
    }

    /**
     * @param Trade  $trade
     * @param object $symbol
     * @param int    $stepScale
     * @param string $alreadyAcquired
     * @param string $leftToSell
     *
     * @return array
     */
    protected function calculatePossibleSellOrders(
        Trade $trade,
        object $symbol,
        int $stepScale,
        string $alreadyAcquired,
        string $leftToSell
    ): array {
        $takeProfits = $trade->getTakeProfits()->toArray();
        $orders = [];

        // sort them on price descending, so the furthers tp point comes first
        usort($takeProfits, static function (TakeProfit $a, TakeProfit $b) {
            return $b->getPrice() <=> $a->getPrice();
        });

        /** @var TakeProfit $takeProfit */
        foreach ($takeProfits as $takeProfit) {
            if ($takeProfit->getPercentage() >= 100) {
                $size = $this->formatter->roundStep($symbol, $alreadyAcquired);
            } else {
                $size = bcmul(
                    $alreadyAcquired,
                    sprintf('0.%s', str_pad($takeProfit->getPercentage(), 2, STR_PAD_LEFT)),
                    $stepScale
                );
            }

            // if the desired chunk is less than what's left, override and break the loop
            if (bccomp($size, $leftToSell, $stepScale) === 1) {
                $orders[] = ['quantity' => $leftToSell, 'price' => $takeProfit->getPrice(), 'tp' => $takeProfit];
                break;
            }

            $orders[] = ['quantity' => $size, 'price' => $takeProfit->getPrice(), 'tp' => $takeProfit];
            $leftToSell = bcsub($leftToSell, $size, $stepScale);
        }

        return $orders;
    }
}
