<?php

namespace Tests\App\Bus\MessageHandler\Command;

use App\Component\ExchangePriceFormatter;
use App\Model\ExchangeOrder;
use App\Model\Symbol;
use App\Model\Trade;
use App\Repository\ExchangeOrderRepository;
use App\Repository\SymbolRepository;
use App\Repository\TradeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EvaluatePositionsHandlerTest extends TestCase
{
    /** @var MockObject|CacheItemPoolInterface */
    protected $pool;
    /** @var MockObject|LoggerInterface */
    protected $logger;
    /** @var ObjectManager|MockObject */
    protected $manager;
    /** @var ExchangePriceFormatter|MockObject */
    protected $formatter;
    /** @var MockObject|MessageBusInterface */
    protected $queryBus;
    /** @var MockObject|MessageBusInterface */
    protected $commandBus;
    /** @var TradeRepository|MockObject */
    protected $tradeRepository;
    /** @var ExchangeOrderRepository|MockObject */
    protected $orderRepository;
    /** @var SymbolRepository|MockObject */
    protected $symbolRepository;
    /** @var ExchangeOrder[]|MockObject[] */
    protected $buyOrders;
    /** @var ExchangeOrder[]|MockObject[] */
    protected $sellOrders;

    protected function setUp()
    {
        parent::setUp();

        $this->pool = $this->createMock(CacheItemPoolInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->manager = $this->createMock(ObjectManager::class);
        $this->formatter = $this->createMock(ExchangePriceFormatter::class);
        $this->queryBus = $this->createMock(MessageBusInterface::class);
        $this->commandBus = $this->createMock(MessageBusInterface::class);

        $this->tradeRepository = $this->createMock(TradeRepository::class);
        $this->orderRepository = $this->createMock(ExchangeOrderRepository::class);
        $this->symbolRepository = $this->createMock(SymbolRepository::class);

        $this->manager->method('getRepository')->willReturnMap([
            [Trade::class, $this->tradeRepository],
            [ExchangeOrder::class, $this->orderRepository],
            [Symbol::class, $this->symbolRepository],
        ]);
    }

    // nog geen sell orders, nog niets aangekocht -> niets doen
    public function testNoBuysNoSells(): void
    {
    }

    // nog geen sell orders, wel al dingen aangekocht -> orders plaatsen
    public function testNoSellsButFilledBuys(): void
    {
    }

    // wel sell orders maar allemaal cancelled -> plaatsen orders
    public function testAllCancelledSells(): void
    {
    }

    // een deel verkocht al, nog geen nieuwe buys gevuld -> geen cancel en zo laten
    public function testSoldAllAcquired(): void
    {
    }

    // een deel verkocht al, wel nieuwe buy orders gevuld -> cancel & nieuwe orders
    public function testAlreadySoldSomeButAcquiredMore(): void
    {
    }

    // alles verkocht al, nog geen nieuwe buys gevuld -> geen cancel en zo laten
    public function testSoldSomeNothingNewAcquired(): void
    {
    }

    // alles verkocht al, wel nieuwe buy orders gevuld -> cancel & nieuwe orders
    public function testAllFilledSellsAcquiredMore(): void
    {
    }

    // een deel verkocht -> cancel alle buy orders
    public function testPartiallyTookProfitCancelAllBuys(): void
    {
    }

    // wel sell orders maar nog niets verkocht, evenveel te koop als aangekocht -> geen cancel, zo laten
    public function testEverythingAcquiredIsBeingSold(): void
    {
    }

    // wel sell orders maar nog niets verkocht, meer gekocht inmiddels -> cancel, nieuwe orders
    public function testActiveSellsNoFillsAcquiredMore(): void
    {
    }

    /**
     * @param string $quantity
     * @param string $filledQuantity
     * @param string $status
     *
     * @return ExchangeOrder
     */
    protected function createOrder(string $quantity, string $filledQuantity, string $status): ExchangeOrder
    {
        return
            (new ExchangeOrder())
            ->setStatus($status)
            ->setQuantity($quantity)
            ->setFilledQuantity($filledQuantity);
    }
}
