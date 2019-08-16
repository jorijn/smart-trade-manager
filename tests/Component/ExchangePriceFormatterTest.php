<?php

namespace Tests\App\Component;

use App\Component\ExchangePriceFormatter;
use App\Exception\FilterNotFoundException;
use App\Exception\FilterParameterNotFoundException;
use App\Model\Symbol;
use App\Model\SymbolFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \App\Component\ExchangePriceFormatter
 */
class ExchangePriceFormatterTest extends TestCase
{
    /** @var MockObject|LoggerInterface */
    protected $logger;
    /** @var ExchangePriceFormatter */
    protected $formatter;

    /**
     * @covers ::roundStep
     * @covers ::getFilterFromSymbol
     * @covers ::getParameterFromFilter
     * @dataProvider providerOfRoundStepCases
     *
     * @param string $stepSize
     * @param string $quantity
     * @param string $expectation
     */
    public function testRoundStep(string $stepSize, string $quantity, string $expectation): void
    {
        $filter = $this->createMock(SymbolFilter::class);
        $symbol = $this->createMock(Symbol::class);

        $symbol->expects(self::once())->method('getFilter')->willReturn($filter);
        $filter->expects(self::once())->method('getParameter')->with('stepSize')->willReturn($stepSize);

        $this->assertSame($expectation, $this->formatter->roundStep($symbol, $quantity));
    }

    /**
     * @covers ::roundStep
     * @covers ::getFilterFromSymbol
     * @covers ::getParameterFromFilter
     * @dataProvider providerOfRoundStepCases
     *
     * @param string $stepSize
     * @param string $quantity
     * @param string $expectation
     */
    public function testRoundStepFailsWhenFilterCannotBeFound(
        string $stepSize,
        string $quantity,
        string $expectation
    ): void {
        $symbol = $this->createMock(Symbol::class);
        $symbol->expects(self::once())->method('getFilter')->willReturn(null);

        $this->logger->expects(self::once())->method('warning');
        $this->expectException(FilterNotFoundException::class);

        $this->formatter->roundStep($symbol, $quantity);
    }

    /**
     * @covers ::roundStep
     * @covers ::getFilterFromSymbol
     * @covers ::getParameterFromFilter
     * @dataProvider providerOfRoundStepCases
     *
     * @param string $stepSize
     * @param string $quantity
     * @param string $expectation
     */
    public function testRoundStepFailsWhenAttributeCannotBeFound(
        string $stepSize,
        string $quantity,
        string $expectation
    ): void {
        $filter = $this->createMock(SymbolFilter::class);
        $symbol = $this->createMock(Symbol::class);

        $symbol->expects(self::once())->method('getFilter')->willReturn($filter);
        $filter->expects(self::once())->method('getParameter')->with('stepSize')->willReturn(null);

        $this->logger->expects(self::once())->method('warning');
        $this->expectException(FilterParameterNotFoundException::class);

        $this->formatter->roundStep($symbol, $quantity);
    }

    /**
     * @return array
     */
    public function providerOfRoundStepCases(): array
    {
        return [
            ['0.00000100', '12345.123456789', '12345.123456'],
            ['0.00001000', '12345.123456789', '12345.12345'],
            ['0.00100000', '12345.123456789', '12345.123'],
            ['0.01000000', '12345.123456789', '12345.12'],
            ['0.10000000', '12345.123456789', '12345.1'],
            ['1.00000000', '12345.123456789', '12345'],
        ];
    }

    /**
     * @return array
     */
    public function providerOfRoundTicksCases(): array
    {
        return [
            ['0.01000000', '12345.123456789', '12345.12'],
            ['0.00100000', '12345.123456789', '12345.123'],
            ['0.00010000', '12345.123456789', '12345.1235'],
            ['0.00001000', '12345.123456789', '12345.12346'],
            ['0.00000100', '12345.123456789', '12345.123457'],
            ['0.00000010', '12345.123456789', '12345.1234568'],
            ['0.00000001', '12345.123456789', '12345.12345679'],
        ];
    }

    /**
     * @dataProvider providerOfRoundTicksCases
     *
     * @param string $tickSize
     * @param string $price
     * @param string $expectation
     */
    public function testRoundTicks(string $tickSize, string $price, string $expectation): void
    {
        $filter = $this->createMock(SymbolFilter::class);
        $symbol = $this->createMock(Symbol::class);

        $symbol->expects(self::once())->method('getFilter')->willReturn($filter);
        $filter->expects(self::once())->method('getParameter')->with('tickSize')->willReturn($tickSize);

        $this->assertSame($expectation, $this->formatter->roundTicks($symbol, $price));
    }

    /**
     * @covers ::roundStep
     * @covers ::getFilterFromSymbol
     * @covers ::getParameterFromFilter
     * @dataProvider providerOfRoundTicksCases
     *
     * @param string $tickSize
     * @param string $price
     * @param string $expectation
     */
    public function testRoundTicksFailsWhenFilterCannotBeFound(
        string $tickSize,
        string $price,
        string $expectation
    ): void {
        $symbol = $this->createMock(Symbol::class);
        $symbol->expects(self::once())->method('getFilter')->willReturn(null);

        $this->logger->expects(self::once())->method('warning');
        $this->expectException(FilterNotFoundException::class);

        $this->formatter->roundStep($symbol, $price);
    }

    /**
     * @covers ::roundStep
     * @covers ::getFilterFromSymbol
     * @covers ::getParameterFromFilter
     * @dataProvider providerOfRoundTicksCases
     *
     * @param string $tickSize
     * @param string $price
     * @param string $expectation
     */
    public function testRoundTicksFailsWhenAttributeCannotBeFound(
        string $tickSize,
        string $price,
        string $expectation
    ): void {
        $filter = $this->createMock(SymbolFilter::class);
        $symbol = $this->createMock(Symbol::class);

        $symbol->expects(self::once())->method('getFilter')->willReturn($filter);
        $filter->expects(self::once())->method('getParameter')->with('tickSize')->willReturn(null);

        $this->logger->expects(self::once())->method('warning');
        $this->expectException(FilterParameterNotFoundException::class);

        $this->formatter->roundTicks($symbol, $price);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->formatter = new ExchangePriceFormatter($this->logger);
    }
}
