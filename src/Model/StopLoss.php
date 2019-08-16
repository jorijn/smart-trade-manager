<?php

namespace App\Model;

class StopLoss
{
    /** @var int|null */
    protected $id;
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
     * @return StopLoss
     */
    public function setId(?int $id): StopLoss
    {
        $this->id = $id;

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
     * @return StopLoss
     */
    public function setPrice(string $price): StopLoss
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
     * @return StopLoss
     */
    public function setTrade(Trade $trade): StopLoss
    {
        $this->trade = $trade;

        return $this;
    }
}
