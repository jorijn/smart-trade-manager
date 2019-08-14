<?php

namespace App\Model;

use PHP\Math\BigNumber\BigNumber;

class StopLoss
{
    /** @var int|null */
    protected $id;
    /** @var string|BigNumber */
    protected $price;
    /** @var Trade */
    protected $trade;

    /**
     * @param string|BigNumber $price
     */
    public function __construct($price)
    {
        $this->price = $price;
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
     */
    public function setTrade(Trade $trade): void
    {
        $this->trade = $trade;
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

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}
