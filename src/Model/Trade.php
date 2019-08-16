<?php

namespace App\Model;

class Trade
{
    /** @var int|null */
    protected $id;
    /** @var string */
    protected $symbol;
    /** @var string */
    protected $quantity;
    /** @var StopLoss|null */
    protected $stoploss;
    /** @var string */
    protected $entryLow;
    /** @var string|null */
    protected $entryHigh;
    /** @var TakeProfit[] */
    protected $takeProfits;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     *
     * @return Trade
     */
    public function setId(?int $id): Trade
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
     * @return string
     */
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     *
     * @return Trade
     */
    public function setQuantity(string $quantity): Trade
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
     * @return string
     */
    public function getEntryLow(): string
    {
        return $this->entryLow;
    }

    /**
     * @param string $entryLow
     *
     * @return Trade
     */
    public function setEntryLow(string $entryLow): Trade
    {
        $this->entryLow = $entryLow;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEntryHigh(): ?string
    {
        return $this->entryHigh;
    }

    /**
     * @param string|null $entryHigh
     *
     * @return Trade
     */
    public function setEntryHigh(?string $entryHigh): Trade
    {
        $this->entryHigh = $entryHigh;

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
