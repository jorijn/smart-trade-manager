<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;

class Trade
{
    /** @var int|null */
    protected $id;
    /** @var string */
    protected $symbol;
    /** @var string */
    protected $quantity;
    /** @var string|null */
    protected $stoploss;
    /** @var string */
    protected $entryLow;
    /** @var string|null */
    protected $entryHigh;
    /** @var TakeProfit[] */
    protected $takeProfits;
    /** @var bool */
    protected $active = true;
    /** @var ExchangeOrder[] */
    protected $orders;

    public function __construct()
    {
        $this->takeProfits = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

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
    public function getSymbol(): ?string
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
    public function getQuantity(): ?string
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
     * @return string
     */
    public function getEntryLow(): ?string
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
     * @return TakeProfit[]|ArrayCollection
     */
    public function getTakeProfits()
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
        $trade = $this;
        $this->takeProfits = array_map(static function ($takeProfit) use ($trade) {
            $takeProfit->setTrade($trade);

            return $takeProfit;
        }, $takeProfits);

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return Trade
     */
    public function setActive(bool $active): Trade
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStoploss(): ?string
    {
        return $this->stoploss;
    }

    /**
     * @param string|null $stoploss
     *
     * @return Trade
     */
    public function setStoploss(?string $stoploss): Trade
    {
        $this->stoploss = $stoploss;

        return $this;
    }
}
