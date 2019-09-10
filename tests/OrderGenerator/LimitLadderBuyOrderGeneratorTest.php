<?php

namespace App\Tests\OrderGenerator;

use App\Component\ExchangePriceFormatter;
use App\OrderGenerator\LimitLadderBuyOrderGenerator;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionMethod;

/**
 * @coversDefaultClass \App\OrderGenerator\LimitLadderBuyOrderGenerator
 */
class LimitLadderBuyOrderGeneratorTest extends TestCase
{
    /** @var ExchangePriceFormatter|MockObject */
    protected $formatter;
    /** @var ObjectManager|MockObject */
    protected $manager;
    /** @var int */
    protected $ladderSize;
    /** @var LimitLadderBuyOrderGenerator */
    protected $generator;
    /** @var MockObject|LoggerInterface */
    protected $logger;

    /**
     * @return array
     */
    public function providerOfCorrectLadders(): array
    {
        return [
            'TRXUSDT' => [
                '0.0156',
                '0.0159',
                '0.10000000',
                '10.00000000',
                1,
                5,
                '1000',
            ],
            'BTCUSDT' => [
                '7000',
                '8000',
                '0.00000100',
                '10.00000000',
                6,
                2,
                '1000',
            ],
        ];
    }

    /**
     * @param string $entryLow
     * @param string $entryHigh
     * @param string $minQty
     * @param string $minNotional
     * @param int    $stepScale
     * @param int    $priceScale
     * @param string $quantity
     *
     * @dataProvider providerOfCorrectLadders
     */
    public function testCorrectnessOfLadders(
        string $entryLow,
        string $entryHigh,
        string $minQty,
        string $minNotional,
        int $stepScale,
        int $priceScale,
        string $quantity
    ): void {
        $ladder = $this->callMethodWithRestrictedVisibility(
            $this->generator,
            'calculateLadder',
            $minQty,
            $minNotional,
            $stepScale,
            $priceScale,
            $entryLow,
            $entryHigh,
            $quantity
        );

        foreach ($ladder as $set) {
            // TODO fixme
        }
    }

    protected function callMethodWithRestrictedVisibility($unitUnderTest, $method, ...$arguments)
    {
        $callable = new ReflectionMethod($unitUnderTest, $method);
        $callable->setAccessible(true);

        return $callable->invokeArgs($unitUnderTest, $arguments);
    }

    protected function setUp()
    {
        $this->formatter = $this->createMock(ExchangePriceFormatter::class);
        $this->manager = $this->createMock(ObjectManager::class);
        $this->ladderSize = 10;
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->generator = new LimitLadderBuyOrderGenerator($this->formatter, $this->manager, $this->ladderSize);
        $this->generator->setLogger($this->logger);
    }
}
