<?php

namespace App\Model;

use PHP\Math\BigNumber\BigNumber;

class Trade
{
    /** @var int|null */
    protected $id;
    /** @var string */
    protected $symbol;
    /** @var string|BigNumber */
    protected $quantity;
    /** @var StopLoss|null */
    protected $stoploss;
    /** @var BigNumber|string */
    protected $entryLow;
    /** @var BigNumber|string|null */
    protected $entryHigh;
    /** @var TakeProfit[] */
    protected $takeProfits;

    /**
     * @return BigNumber|string
     */
    public function getEntryLow()
    {
        return $this->entryLow;
    }

    /**
     * @param BigNumber|string $entryLow
     *
     * @return Trade
     */
    public function setEntryLow($entryLow)
    {
        $this->entryLow = $entryLow;

        return $this;
    }

    /**
     * @return BigNumber|string|null
     */
    public function getEntryHigh()
    {
        return $this->entryHigh;
    }

    /**
     * @param BigNumber|string|null $entryHigh
     *
     * @return Trade
     */
    public function setEntryHigh($entryHigh)
    {
        $this->entryHigh = $entryHigh;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Trade
     */
    public function setId(int $id): Trade
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     *
     * @return Trade
     */
    public function setSymbol(string $symbol): Trade
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return BigNumber|string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param BigNumber|string $quantity
     *
     * @return Trade
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return StopLoss|null
     */
    public function getStoploss(): ?StopLoss
    {
        return $this->stoploss;
    }

    /**
     * @param StopLoss|null $stoploss
     *
     * @return Trade
     */
    public function setStoploss(?StopLoss $stoploss): Trade
    {
        $this->stoploss = $stoploss;

        return $this;
    }

    /**
     * @return TakeProfit[]
     */
    public function getTakeProfits(): array
    {
        return $this->takeProfits;
    }

    /**
     * @param TakeProfit[] $takeProfits
     *
     * @return Trade
     */
    public function setTakeProfits(array $takeProfits): Trade
    {
        $this->takeProfits = $takeProfits;

        return $this;
    }
}
