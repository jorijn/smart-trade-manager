<?php

namespace Tests\App\Bus\MessageHandler\Command;

use App\Bus\Message\Command\CancelExchangeOrdersCommand;
use App\Bus\Message\Command\CreateExchangeOrdersCommand;
use App\Bus\Message\Command\EvaluatePositionsCommand;
use App\Bus\Message\Query\SellOrderQuery;
use App\Bus\MessageHandler\Command\EvaluatePositionsHandler;
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
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

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
    /** @var EvaluatePositionsHandler */
    protected $handler;
    /** @var EvaluatePositionsCommand */
    protected $command;
    /** @var Trade|MockObject */
    protected $trade;
    /** @var Symbol|MockObject */
    protected $symbol;
    /** @var string */
    protected $stepScale;
    /** @var int */
    private $tradeId;

    public function testDoesNotRunWhenNotLatest(): void
    {
        $pool = $this->createMock(CacheItemPoolInterface::class);
        $item = $this->createMock(CacheItemInterface::class);
        $item->expects(self::exactly(2))->method('isHit')->willReturnOnConsecutiveCalls(true, false);
        $item->expects(self::once())->method('get')->willReturn('k'.mt_rand());

        $pool->expects(self::exactly(2))->method('getItem')
            ->with(str_replace('\\', '_', get_class($this->command)))
            ->willReturn($item);

        $this->handler = new EvaluatePositionsHandler(
            $pool,
            $this->logger,
            $this->manager,
            $this->formatter,
            $this->queryBus,
            $this->commandBus
        );

        $this->logger->expects(self::exactly(2))->method('debug')->with('evaluating positions: probably old command, newer will follow');
        $this->tradeRepository->expects(self::never())->method('getPendingTrades');

        $this->handler->__invoke($this->command);
        $this->handler->__invoke($this->command); // second one will simulate cache miss
    }

    public function testNoActiveTradesDoesNothing(): void
    {
        $this->tradeRepository->expects(self::once())->method('getPendingTrades')->willReturn([]);
        $this->symbolRepository->expects(self::never())->method('find');

        $this->handler->__invoke($this->command);
    }

    public function testNoBuysNoSells(): void
    {
        $this->tradeRepository->method('getPendingTrades')->willReturn([$this->trade]);
        $this->orderRepository->expects(self::once())->method('findBuyOrders')->with($this->trade)->willReturn([
            $this->createOrder('0.00001', '0.00000', 'NEW'),
            $this->createOrder('0.00001', '0.00000', 'NEW'),
            $this->createOrder('0.00001', '0.00000', 'NEW'),
        ]);

        $this->trade->expects(self::never())->method('getTakeProfits');

        $this->handler->__invoke($this->command);
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

    public function testNoSellsButFilledBuys(): void
    {
        $this->tradeRepository->method('getPendingTrades')->willReturn([$this->trade]);
        $this->orderRepository->expects(self::once())->method('findBuyOrders')->with($this->trade)->willReturn([
            $this->createOrder('0.00002', '0.00001', 'PARTIALLY_FILLED'),
            $this->createOrder('0.00001', '0.00001', 'FILLED'),
            $this->createOrder('0.00001', '0.00000', 'NEW'),
        ]);

        $this->trade->expects(self::once())->method('getTakeProfits')->willReturn([1]); // should only trigger the count
        $this->orderRepository->expects(self::once())->method('findTakeProfitOrders')->with($this->trade)->willReturn([]);

        $sellOrders = ['b'.mt_rand(), 'c'.mt_rand()];
        $stamp = new HandledStamp($sellOrders, __CLASS__);
        $envelope = new Envelope(new \stdClass(), [$stamp]);

        $this->queryBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (SellOrderQuery $sellOrderQuery) {
                return $sellOrderQuery->getTradeId() === $this->tradeId;
            }))
            ->willReturn($envelope);

        $this->commandBus->expects(self::once())->method('dispatch')->with(self::callback(static function (
            CreateExchangeOrdersCommand $command
        ) use (
            $sellOrders
        ) {
            return $command->getOrders() === $sellOrders;
        }))
            ->willReturn(new Envelope(new \stdClass()));

        $this->handler->__invoke($this->command);
    }

    public function testAllCancelledSells(): void
    {
        $this->tradeRepository->method('getPendingTrades')->willReturn([$this->trade]);
        $this->orderRepository->expects(self::once())->method('findBuyOrders')->with($this->trade)->willReturn([
            $this->createOrder('0.00002', '0.00001', 'PARTIALLY_FILLED'),
            $this->createOrder('0.00001', '0.00001', 'FILLED'),
            $this->createOrder('0.00001', '0.00000', 'NEW'),
        ]);

        $this->trade->expects(self::once())->method('getTakeProfits')->willReturn([1]); // should only trigger the count
        $this->orderRepository->expects(self::once())->method('findTakeProfitOrders')->with($this->trade)->willReturn([
            $this->createOrder('0.00002', '0.00000', 'CANCELLED'),
            $this->createOrder('0.00001', '0.00000', 'CANCELLED'),
            $this->createOrder('0.00001', '0.00000', 'CANCELLED'),
        ]);

        $sellOrders = ['b'.mt_rand(), 'c'.mt_rand()];
        $stamp = new HandledStamp($sellOrders, __CLASS__);
        $envelope = new Envelope(new \stdClass(), [$stamp]);

        $this->queryBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (SellOrderQuery $sellOrderQuery) {
                return $sellOrderQuery->getTradeId() === $this->tradeId;
            }))
            ->willReturn($envelope);

        $this->commandBus->expects(self::once())->method('dispatch')->with(self::callback(static function (
            CreateExchangeOrdersCommand $command
        ) use (
            $sellOrders
        ) {
            return $command->getOrders() === $sellOrders;
        }))
            ->willReturn(new Envelope(new \stdClass()));

        $this->handler->__invoke($this->command);
    }

    public function testSoldAllAcquired(): void
    {
        $this->tradeRepository->method('getPendingTrades')->willReturn([$this->trade]);
        $this->orderRepository->expects(self::atLeastOnce())->method('findBuyOrders')->with($this->trade)->willReturn([
            $a = $this->createOrder('0.00002', '0.00001', 'PARTIALLY_FILLED'),
            $b = $this->createOrder('0.00001', '0.00001', 'FILLED'),
            $c = $this->createOrder('0.00001', '0.00000', 'NEW'),
        ]);

        $this->trade->expects(self::once())->method('getTakeProfits')->willReturn([1]); // should only trigger the count
        $this->orderRepository->expects(self::once())->method('findTakeProfitOrders')->with($this->trade)->willReturn([
            $this->createOrder('0.00002', '0.00002', 'FILLED'),
        ]);

        $this->queryBus
            ->expects(self::never())
            ->method('dispatch');

        $this->commandBus->expects(self::once())->method('dispatch')->with(self::callback(static function (
            CancelExchangeOrdersCommand $command
        ) use (
            $a,
            $c
        ) {
            self::assertContains($a, $command->getOrders());
            self::assertContains($c, $command->getOrders());

            return true;
        }))
            ->willReturn(new Envelope(new \stdClass()));

        $this->handler->__invoke($this->command);
    }

    public function testAlreadySoldSomeButAcquiredMore(): void
    {
        $this->tradeRepository->method('getPendingTrades')->willReturn([$this->trade]);
        $this->orderRepository->expects(self::atLeastOnce())->method('findBuyOrders')->with($this->trade)->willReturn([
            $a = $this->createOrder('0.00003', '0.00002', 'PARTIALLY_FILLED'),
            $b = $this->createOrder('0.00001', '0.00001', 'FILLED'),
            $c = $this->createOrder('0.00001', '0.00000', 'NEW'),
        ]);

        $this->trade->expects(self::once())->method('getTakeProfits')->willReturn([1]); // should only trigger the count
        $this->orderRepository->expects(self::once())->method('findTakeProfitOrders')->with($this->trade)->willReturn([
            $this->createOrder('0.00002', '0.00002', 'FILLED'),
        ]);

        $sellOrders = ['b'.mt_rand(), 'c'.mt_rand()];
        $stamp = new HandledStamp($sellOrders, __CLASS__);
        $envelope = new Envelope(new \stdClass(), [$stamp]);

        $this->commandBus->expects(self::exactly(2))->method('dispatch')->withConsecutive(
            [
                self::callback(static function (
                    CancelExchangeOrdersCommand $command
                ) use (
                    $a,
                    $c
                ) {
                    self::assertContains($a, $command->getOrders());
                    self::assertContains($c, $command->getOrders());

                    return true;
                }),
            ],
            [
                self::callback(static function (
                    CreateExchangeOrdersCommand $command
                ) use (
                    $sellOrders
                ) {
                    return $command->getOrders() === $sellOrders;
                }),
            ]
        )
            ->willReturn(new Envelope(new \stdClass()));

        $this->queryBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (SellOrderQuery $sellOrderQuery) {
                return $sellOrderQuery->getTradeId() === $this->tradeId;
            }))
            ->willReturn($envelope);

        $this->handler->__invoke($this->command);
    }

    public function testAllFilledSellsAcquiredMore(): void
    {
        $this->tradeRepository->method('getPendingTrades')->willReturn([$this->trade]);
        $this->orderRepository->expects(self::atLeastOnce())->method('findBuyOrders')->with($this->trade)->willReturn([
            $a = $this->createOrder('0.00003', '0.00003', 'FILLED'),
            $b = $this->createOrder('0.00001', '0.00001', 'FILLED'),
            $c = $this->createOrder('0.00001', '0.00000', 'NEW'),
        ]);

        $this->trade->expects(self::once())->method('getTakeProfits')->willReturn([1]); // should only trigger the count
        $this->orderRepository->expects(self::once())->method('findTakeProfitOrders')->with($this->trade)->willReturn([
            $this->createOrder('0.00003', '0.00003', 'FILLED'),
        ]);

        $sellOrders = ['b'.mt_rand(), 'c'.mt_rand()];
        $stamp = new HandledStamp($sellOrders, __CLASS__);
        $envelope = new Envelope(new \stdClass(), [$stamp]);

        $this->commandBus->expects(self::exactly(2))->method('dispatch')->withConsecutive(
            [
                self::callback(static function (
                    CancelExchangeOrdersCommand $command
                ) use ($c) {
                    self::assertContains($c, $command->getOrders());
                    self::assertCount(1, $command->getOrders());

                    return true;
                }),
            ],
            [
                self::callback(static function (
                    CreateExchangeOrdersCommand $command
                ) use (
                    $sellOrders
                ) {
                    return $command->getOrders() === $sellOrders;
                }),
            ]
        )
            ->willReturn(new Envelope(new \stdClass()));

        $this->queryBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (SellOrderQuery $sellOrderQuery) {
                return $sellOrderQuery->getTradeId() === $this->tradeId;
            }))
            ->willReturn($envelope);

        $this->handler->__invoke($this->command);
    }

    public function testEverythingAcquiredIsBeingSold(): void
    {
        $this->tradeRepository->method('getPendingTrades')->willReturn([$this->trade]);
        $this->orderRepository->expects(self::atLeastOnce())->method('findBuyOrders')->with($this->trade)->willReturn([
            $a = $this->createOrder('0.00003', '0.00003', 'FILLED'),
            $b = $this->createOrder('0.00001', '0.00001', 'FILLED'),
            $c = $this->createOrder('0.00001', '0.00000', 'NEW'),
        ]);

        $this->trade->expects(self::once())->method('getTakeProfits')->willReturn([1]); // should only trigger the count
        $this->orderRepository->expects(self::once())->method('findTakeProfitOrders')->with($this->trade)->willReturn([
            $this->createOrder('0.00004', '0.00000', 'NEW'),
        ]);

        $sellOrders = ['b'.mt_rand(), 'c'.mt_rand()];
        $stamp = new HandledStamp($sellOrders, __CLASS__);
        $envelope = new Envelope(new \stdClass(), [$stamp]);

        $this->commandBus->expects(self::never())->method('dispatch');
        $this->queryBus->expects(self::never())->method('dispatch');

        $this->handler->__invoke($this->command);
    }

    public function testActiveSellsNoFillsAcquiredMore(): void
    {
        $this->tradeRepository->method('getPendingTrades')->willReturn([$this->trade]);
        $this->orderRepository->expects(self::atLeastOnce())->method('findBuyOrders')->with($this->trade)->willReturn([
            $this->createOrder('0.00003', '0.00003', 'FILLED'),
            $this->createOrder('0.00001', '0.00001', 'FILLED'),
            $this->createOrder('0.00001', '0.00000', 'NEW'),
        ]);

        $this->trade->expects(self::once())->method('getTakeProfits')->willReturn([1]); // should only trigger the count
        $this->orderRepository->expects(self::once())->method('findTakeProfitOrders')->with($this->trade)->willReturn([
            $a = $this->createOrder('0.00003', '0.00000', 'NEW'),
        ]);

        $sellOrders = ['b'.mt_rand(), 'c'.mt_rand()];
        $stamp = new HandledStamp($sellOrders, __CLASS__);
        $envelope = new Envelope(new \stdClass(), [$stamp]);

        $this->commandBus->expects(self::exactly(2))->method('dispatch')->withConsecutive(
            [
                self::callback(static function (
                    CancelExchangeOrdersCommand $command
                ) use ($a) {
                    self::assertContains($a, $command->getOrders());
                    self::assertCount(1, $command->getOrders());

                    return true;
                }),
            ],
            [
                self::callback(static function (
                    CreateExchangeOrdersCommand $command
                ) use (
                    $sellOrders
                ) {
                    return $command->getOrders() === $sellOrders;
                }),
            ]
        )
            ->willReturn(new Envelope(new \stdClass()));

        $this->queryBus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(function (SellOrderQuery $sellOrderQuery) {
                return $sellOrderQuery->getTradeId() === $this->tradeId;
            }))
            ->willReturn($envelope);

        $this->handler->__invoke($this->command);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->pool = $this->createMock(CacheItemPoolInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->manager = $this->createMock(ObjectManager::class);
        $this->formatter = $this->createMock(ExchangePriceFormatter::class);
        $this->queryBus = $this->createMock(MessageBusInterface::class);
        $this->commandBus = $this->createMock(MessageBusInterface::class);
        $this->command = new EvaluatePositionsCommand();
        $this->tradeRepository = $this->createMock(TradeRepository::class);
        $this->orderRepository = $this->createMock(ExchangeOrderRepository::class);
        $this->symbolRepository = $this->createMock(SymbolRepository::class);

        $this->manager->method('getRepository')->willReturnMap([
            [Trade::class, $this->tradeRepository],
            [ExchangeOrder::class, $this->orderRepository],
            [Symbol::class, $this->symbolRepository],
        ]);

        $this->handler = new EvaluatePositionsHandler(
            $this->pool,
            $this->logger,
            $this->manager,
            $this->formatter,
            $this->queryBus,
            $this->commandBus
        );

        $item = $this->createMock(CacheItemInterface::class);
        $item->method('isHit')->willReturn(true);
        $item->method('get')->willReturn($this->command->getKey());

        $this->pool->method('getItem')
            ->with(str_replace('\\', '_', get_class($this->command)))
            ->willReturn($item);

        $this->tradeId = mt_rand();
        $this->trade = $this->createMock(Trade::class);
        $this->trade->method('getId')->willReturn($this->tradeId);
        $this->symbol = $this->createMock(Symbol::class);
        $this->stepScale = 5;

        $this->symbolRepository->method('find')->willReturn($this->symbol);
        $this->formatter->method('getStepScale')->willReturn($this->stepScale);
    }
}
