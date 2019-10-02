<?php

namespace App\Tests\Bus\MessageHandler\Command;

use App\Bus\Message\Command\SynchronizeOrderHistoryCommand;
use App\Bus\MessageHandler\Command\SynchronizeOrderHistoryHandler;
use App\Model\ExchangeOrder;
use App\Repository\ExchangeOrderRepository;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @coversDefaultClass \App\Bus\MessageHandler\Command\SynchronizeOrderHistoryHandler
 */
class SynchronizeOrderHistoryHandlerTest extends TestCase
{
    /** @var ObjectManager|MockObject */
    protected $manager;
    /** @var MockObject|HttpClientInterface */
    protected $binanaceApi;
    /** @var SynchronizeOrderHistoryHandler */
    protected $handler;
    /** @var MockObject|LoggerInterface */
    protected $logger;
    /** @var ExchangeOrderRepository|MockObject */
    protected $exchangeOrderRepository;

    public function testDoesNothingNoPendingSymbols()
    {
        self::markTestIncomplete('TODO');
    }

    public function testPendingSymbolsButNoPendingOrders()
    {
        self::markTestIncomplete('TODO');
    }

    /**
     * @param array $ordersInDB
     * @param array $ordersOnExchange
     * @param array $updatesExpected
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @dataProvider providerOfRegularExchangeOrders
     */
    public function testExchangeOrdersAreUpdated(
        array $ordersInDB,
        array $ordersOnExchange,
        array $updatesExpected
    ): void {
        self::markTestIncomplete('TODO');

        $oldestId = mt_rand();
        $symbol = 's'.mt_rand();

        $this->exchangeOrderRepository
            ->expects(self::once())
            ->method('getSymbolsWithPendingOrders')
            ->willReturn([['symbol' => $symbol, 'oldest_order' => $oldestId]]);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())->method('toArray')->willReturn($ordersOnExchange);

        $this->binanaceApi
            ->expects(self::once())
            ->method('request')
            ->with('GET', 'v3/allOrders', self::callback(static function (array $options) use ($oldestId, $symbol) {
                self::assertArrayHasKey('body', $options);
                self::assertArrayHasKey('symbol', $options['body']);
                self::assertArrayHasKey('orderId', $options['body']);
                self::assertSame($options['body']['symbol'], $symbol);
                self::assertSame($options['body']['orderId'], $oldestId);

                return true;
            }))
            ->willReturn($response);

        $this->exchangeOrderRepository
            ->expects(self::once())
            ->method('findBy')
            ->with(['orderId' => array_keys($ordersOnExchange)])
            ->willReturn($ordersInDB);

        $persistedOrders = [];
        foreach ($ordersInDB as $order) {
            if (in_array($order->getOrderId(), $updatesExpected, true)) {
                $order->expects(self::once())->method('update')->with($ordersOnExchange[$order->getOrderId()]);
                $persistedOrders[] = $order;
            } else {
                $order->expects(self::never())->method('update');
            }
        }

        $this->manager
            ->expects(self::exactly(count($persistedOrders)))
            ->method('persist')
            ->withConsecutive(...$persistedOrders);

        // TODO processHistoryOfOcoExchangeOrders

        $this->handler->__invoke(new SynchronizeOrderHistoryCommand());
    }

    protected function setUp()
    {
        $this->manager = $this->createMock(ObjectManager::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->binanaceApi = $this->createMock(HttpClientInterface::class);
        $this->exchangeOrderRepository = $this->createMock(ExchangeOrderRepository::class);

        $this->manager->method('getRepository')->willReturnMap([
            [ExchangeOrder::class, $this->exchangeOrderRepository],
        ]);

        $this->handler = new SynchronizeOrderHistoryHandler($this->logger, $this->manager, $this->binanaceApi);
    }

    public function providerOfRegularExchangeOrders(): array
    {
        return [
            'order 2 & 3 are newer on remote' => [
                [
                    $this->createOrder(100, '1'),
                    $this->createOrder(100, '2'),
                    $this->createOrder(100, '3'),
                ],
                [
                    '1' => ['orderId' => '1', 'updateTime' => 90],
                    '2' => ['orderId' => '2', 'updateTime' => 110],
                    '3' => ['orderId' => '3', 'updateTime' => 110],
                ],
                ['2', '3'],
            ],
        ];
    }

    /**
     * @param string $orderId
     * @param int    $updatedAt
     *
     * @return ExchangeOrder
     */
    protected function createOrder(int $updatedAt, string $orderId): ExchangeOrder
    {
        $order = $this->createMock(ExchangeOrder::class);
        $order->method('getUpdatedAt')->willReturn($updatedAt);
        $order->method('getOrderId')->willReturn($orderId);

        return $order;
    }
}
