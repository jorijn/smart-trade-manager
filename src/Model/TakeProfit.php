<?php

namespace App\Model;

use PHP\Math\BigNumber\BigNumber;

class TakeProfit
{
    /** @var int|null */
    protected $id;
    /** @var string|null */
    protected $quantity;
    /** @var int */
    protected $percentage;
    /** @var string|BigNumber */
    protected $price;
    /** @var Trade */
    protected $trade;

    /**
     * @param string|BigNumber $price
     * @param int              $percentage
     */
    public function __construct($price, int $percentage = 100)
    {
        $this->price = $price;
        $this->percentage = $percentage;
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
     * @return TakeProfit
     */
    public function setId(int $id): TakeProfit
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    /**
     * @param string|null $quantity
     *
     * @return TakeProfit
     */
    public function setQuantity(?string $quantity): TakeProfit
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getPercentage(): int
    {
        return $this->percentage;
    }

    /**
     * @param int $percentage
     *
     * @return TakeProfit
     */
    public function setPercentage(int $percentage): TakeProfit
    {
        $this->percentage = $percentage;

        return $this;
    }

    /**
     * @return Trade
     */
    public function getTrade(): Trade
    {
        return $this->trade;
    }

    /**
     * @param Trade $trade
     *
     * @return TakeProfit
     */
    public function setTrade(Trade $trade): TakeProfit
    {
        $this->trade = $trade;

        return $this;
    }

    /**
     * @return BigNumber|string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param BigNumber|string $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }
}
