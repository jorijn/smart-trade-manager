<?php

namespace App\Tests\Bus\MessageHandler\Command;

use App\Bus\Message\Command\CancelExchangeOrdersCommand;
use App\Bus\MessageHandler\Command\CancelExchangeOrdersHandler;
use App\Exception\BinanceApiException;
use App\Model\ExchangeOrderInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @coversDefaultClass \App\Bus\MessageHandler\Command\CancelExchangeOrdersHandler
 * @covers ::__construct
 */
class CancelExchangeOrdersHandlerTest extends TestCase
{
    /** @var MockObject|HttpClientInterface */
    protected $httpClient;
    /** @var MockObject|LoggerInterface */
    protected $logger;
    /** @var ObjectManager|MockObject */
    protected $manager;
    /** @var MockObject|EventDispatcherInterface */
    protected $dispatcher;
    /** @var CancelExchangeOrdersHandler */
    protected $handler;

    /**
     * @covers ::__invoke
     */
    public function testMethodCancelsOrders(): void
    {
        $endpoint = 'e'.mt_rand();
        $symbol = 's'.mt_rand();
        $orderId = (string) mt_rand();
        $response = ['r' => mt_rand()];

        $order = $this->createMock(ExchangeOrderInterface::class);
        $order->method('getEndpoint')->willReturn($endpoint);
        $order->method('getSymbol')->willReturn($symbol);
        $order->method('getAttributeIdentifierValue')->willReturn($orderId);
        $order->method('getAttributeIdentifier')->willReturn('orderId');

        $responseObject = $this->createMock(ResponseInterface::class);
        $responseObject->expects(self::once())->method('toArray')->willReturn($response);

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('DELETE', $endpoint, [
                'extra' => ['security_type' => 'TRADE'],
                'body' => [
                    'symbol' => $symbol,
                    'orderId' => $orderId,
                ],
            ])
            ->willReturn($responseObject);

        $this->logger->expects(self::never())->method('error');

        $order->expects(self::once())->method('update')->with($response);
        $this->manager->expects(self::once())->method('persist')->with($order);
        $this->manager->expects(self::once())->method('flush');

        $this->handler->__invoke(new CancelExchangeOrdersCommand($order));
    }

    /**
     * @covers ::__invoke
     */
    public function testMethodLogsExceptions(): void
    {
        $endpoint = 'e'.mt_rand();
        $symbol = 's'.mt_rand();
        $orderId = (string) mt_rand();

        $order = $this->createMock(ExchangeOrderInterface::class);
        $order->method('getEndpoint')->willReturn($endpoint);
        $order->method('getSymbol')->willReturn($symbol);
        $order->method('getAttributeIdentifierValue')->willReturn($orderId);
        $order->method('getAttributeIdentifier')->willReturn('orderId');

        $code = mt_rand();
        $message = 'm'.mt_rand();

        $this->httpClient
            ->expects(self::once())
            ->method('request')
            ->with('DELETE', $endpoint, [
                'extra' => ['security_type' => 'TRADE'],
                'body' => [
                    'symbol' => $symbol,
                    'orderId' => $orderId,
                ],
            ])->willThrowException(new BinanceApiException($message, $code));

        $this->logger->expects(self::once())->method('error')->with(
            'failed to cancel order',
            self::callback(static function (array $context) use (
                $orderId
            ) {
                self::assertArrayHasKey('orderId', $context);
                self::assertSame($context['orderId'], $orderId);

                return true;
            })
        );

        $order->expects(self::never())->method('update');
        $this->manager->expects(self::never())->method('persist');

        $this->handler->__invoke(new CancelExchangeOrdersCommand($order));
    }

    protected function setUp()
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->manager = $this->createMock(ObjectManager::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->handler = new CancelExchangeOrdersHandler(
            $this->httpClient,
            $this->logger,
            $this->manager,
            $this->dispatcher
        );
    }
}
