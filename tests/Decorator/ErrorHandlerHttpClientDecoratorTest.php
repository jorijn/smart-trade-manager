<?php

namespace App\Tests\Decorator;

use App\Decorator\ErrorHandlerHttpClientDecorator;
use App\Exception\BinanceApiException;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @coversDefaultClass \App\Decorator\ErrorHandlerHttpClientDecorator
 */
class ErrorHandlerHttpClientDecoratorTest extends TestCase
{
    /**
     * @covers ::request
     */
    public function testRequestHappyFlow(): void
    {
        $method = 'm'.mt_rand();
        $url = 'u'.mt_rand();
        $options = ['o'.mt_rand()];

        $response = $this->createMock(ResponseInterface::class);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->with($method, $url, $options)
            ->willReturn($response);

        self::assertSame(
            $response,
            (new ErrorHandlerHttpClientDecorator($httpClient))->request($method, $url, $options)
        );
    }

    /**
     * @covers ::request
     */
    public function testRequestThrowsException(): void
    {
        $method = 'm'.mt_rand();
        $url = 'u'.mt_rand();
        $options = ['o'.mt_rand()];

        $exception = new BinanceApiException('m'.mt_rand(), mt_rand());
        $response = $this->createMock(ResponseInterface::class);

        $response
            ->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'msg' => $exception->getMessage(),
                'code' => $exception->getCode()
            ]);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->with($method, $url, $options)
            ->willReturn($response);

        $this->expectExceptionObject($exception);

        (new ErrorHandlerHttpClientDecorator($httpClient))->request($method, $url, $options);
    }
}
