<?php

namespace App\Model;

use PHP\Math\BigNumber\BigNumber;

class Order
{
    public const LIMIT = 'LIMIT';
    public const MARKET = 'MARKET';
    public const STOP_LOSS = 'STOP_LOSS';
    public const STOP_LOSS_LIMIT = 'STOP_LOSS_LIMIT';
    public const TAKE_PROFIT = 'TAKE_PROFIT';
    public const TAKE_PROFIT_LIMIT = 'TAKE_PROFIT_LIMIT';
    public const LIMIT_MAKER = 'LIMIT_MAKER';
    public const BUY = 'BUY';
    public const SELL = 'SELL';

    /** @var string */
    protected $symbol;
    /** @var string */
    protected $side;
    /** @var string */
    protected $type;
    /** @var string|null */
    protected $timeInForce = 'GTC';
    /** @var string|BigNumber */
    protected $quantity;
    /** @var string|BigNumber */
    protected $price;
    /** @var string|null */
    protected $newClientOrderId;
    /** @var string|BigNumber|null */
    protected $stopPrice;
    /** @var string|BigNumber|null */
    protected $icebergQty;
    /** @var string|null */
    protected $newOrderRespType = 'FULL';
    /** @var int|null */
    protected $recvWindow = 60000;
    /** @var string */
    protected $timestamp;

    /**
     * @return array
     */
    public function toApiAttributes(): array
    {
        $attributes = [
            'symbol' => $this->symbol,
            'side' => $this->side,
            'type' => $this->type,
            'quantity' => (string) $this->quantity,
            'price' => (string) $this->price,
            'timestamp' => $this->timestamp,
        ];

        if ($this->timeInForce) {
            $attributes['timeInForce'] = $this->timeInForce;
        }

        if ($this->newClientOrderId) {
            $attributes['newClientOrderId'] = $this->newClientOrderId;
        }

        if ($this->stopPrice) {
            $attributes['stopPrice'] = (string) $this->stopPrice;
        }

        if ($this->icebergQty) {
            $attributes['icebergQty'] = (string) $this->icebergQty;
        }

        if ($this->newOrderRespType) {
            $attributes['newOrderRespType'] = $this->newOrderRespType;
        }

        if ($this->recvWindow) {
            $attributes['recvWindow'] = $this->recvWindow;
        }

        return $attributes;
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
     * @return Order
     */
    public function setSymbol(string $symbol): Order
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return string
     */
    public function getSide(): string
    {
        return $this->side;
    }

    /**
     * @param string $side
     *
     * @return Order
     */
    public function setSide(string $side): Order
    {
        $this->side = $side;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return Order
     */
    public function setType(string $type): Order
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimeInForce(): string
    {
        return $this->timeInForce;
    }

    /**
     * @param string $timeInForce
     *
     * @return Order
     */
    public function setTimeInForce(string $timeInForce): Order
    {
        $this->timeInForce = $timeInForce;

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
     * @return Order
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

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
     *
     * @return Order
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNewClientOrderId(): ?string
    {
        return $this->newClientOrderId;
    }

    /**
     * @param string|null $newClientOrderId
     *
     * @return Order
     */
    public function setNewClientOrderId(?string $newClientOrderId): Order
    {
        $this->newClientOrderId = $newClientOrderId;

        return $this;
    }

    /**
     * @return BigNumber|string
     */
    public function getStopPrice()
    {
        return $this->stopPrice;
    }

    /**
     * @param BigNumber|string $stopPrice
     *
     * @return Order
     */
    public function setStopPrice($stopPrice)
    {
        $this->stopPrice = $stopPrice;

        return $this;
    }

    /**
     * @return BigNumber|string
     */
    public function getIcebergQty()
    {
        return $this->icebergQty;
    }

    /**
     * @param BigNumber|string $icebergQty
     *
     * @return Order
     */
    public function setIcebergQty($icebergQty)
    {
        $this->icebergQty = $icebergQty;

        return $this;
    }

    /**
     * @return string
     */
    public function getNewOrderRespType(): string
    {
        return $this->newOrderRespType;
    }

    /**
     * @param string $newOrderRespType
     *
     * @return Order
     */
    public function setNewOrderRespType(string $newOrderRespType): Order
    {
        $this->newOrderRespType = $newOrderRespType;

        return $this;
    }

    /**
     * @return int
     */
    public function getRecvWindow(): int
    {
        return $this->recvWindow;
    }

    /**
     * @param int $recvWindow
     *
     * @return Order
     */
    public function setRecvWindow(int $recvWindow): Order
    {
        $this->recvWindow = $recvWindow;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     *
     * @return Order
     */
    public function setTimestamp(int $timestamp): Order
    {
        $this->timestamp = $timestamp;

        return $this;
    }
}
