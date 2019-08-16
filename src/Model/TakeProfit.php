<?php

namespace App\Model;

class TakeProfit
{
    /** @var int|null */
    protected $id;
    /** @var string|null */
    protected $quantity;
    /** @var int */
    protected $percentage;
    /** @var string */
    protected $price;
    /** @var Trade */
    protected $trade;

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
     * @return TakeProfit
     */
    public function setId(?int $id): TakeProfit
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
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @param string $price
     *
     * @return TakeProfit
     */
    public function setPrice(string $price): TakeProfit
    {
        $this->price = $price;

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
}
