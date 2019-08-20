<?php

namespace App\Model;

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
    /** @var string */
    protected $quantity;
    /** @var string */
    protected $price;
    /** @var string|null */
    protected $stopPrice;
    /** @var string|null */
    protected $icebergQty;
    /** @var string|null */
    protected $newOrderRespType = 'FULL';
    /** @var int|null */
    protected $recvWindow = 60000;
    /** @var string|null */
    protected $clientOrderId;
    /** @var string|null */
    protected $status;
    /** @var string|null */
    protected $filledQuantity;
    /** @var int */
    protected $updatedAt;
    /** @var string|null */
    protected $filledQuoteQuantity;

    /**
     * @return string|null
     */
    public function getFilledQuoteQuantity(): ?string
    {
        return $this->filledQuoteQuantity;
    }

    /**
     * @param string|null $filledQuoteQuantity
     *
     * @return Order
     */
    public function setFilledQuoteQuantity(?string $filledQuoteQuantity): Order
    {
        $this->filledQuoteQuantity = $filledQuoteQuantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getUpdatedAt(): int
    {
        return $this->updatedAt;
    }

    /**
     * @param int $updatedAt
     *
     * @return Order
     */
    public function setUpdatedAt(int $updatedAt): Order
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

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
        ];

        if ($this->timeInForce) {
            $attributes['timeInForce'] = $this->timeInForce;
        }

        if ($this->stopPrice) {
            $attributes['stopPrice'] = $this->stopPrice;
        }

        if ($this->icebergQty) {
            $attributes['icebergQty'] = $this->icebergQty;
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
     * @return string|null
     */
    public function getTimeInForce(): ?string
    {
        return $this->timeInForce;
    }

    /**
     * @param string|null $timeInForce
     *
     * @return Order
     */
    public function setTimeInForce(?string $timeInForce): Order
    {
        $this->timeInForce = $timeInForce;

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
     * @return Order
     */
    public function setQuantity(string $quantity): Order
    {
        $this->quantity = $quantity;

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
     * @return Order
     */
    public function setPrice(string $price): Order
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStopPrice(): ?string
    {
        return $this->stopPrice;
    }

    /**
     * @param string|null $stopPrice
     *
     * @return Order
     */
    public function setStopPrice(?string $stopPrice): Order
    {
        $this->stopPrice = $stopPrice;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIcebergQty(): ?string
    {
        return $this->icebergQty;
    }

    /**
     * @param string|null $icebergQty
     *
     * @return Order
     */
    public function setIcebergQty(?string $icebergQty): Order
    {
        $this->icebergQty = $icebergQty;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNewOrderRespType(): ?string
    {
        return $this->newOrderRespType;
    }

    /**
     * @param string|null $newOrderRespType
     *
     * @return Order
     */
    public function setNewOrderRespType(?string $newOrderRespType): Order
    {
        $this->newOrderRespType = $newOrderRespType;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRecvWindow(): ?int
    {
        return $this->recvWindow;
    }

    /**
     * @param int|null $recvWindow
     *
     * @return Order
     */
    public function setRecvWindow(?int $recvWindow): Order
    {
        $this->recvWindow = $recvWindow;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientOrderId(): string
    {
        return $this->clientOrderId;
    }

    /**
     * @param string|null $clientOrderId
     *
     * @return Order
     */
    public function setClientOrderId(string $clientOrderId): Order
    {
        $this->clientOrderId = $clientOrderId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     *
     * @return Order
     */
    public function setStatus(?string $status): Order
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilledQuantity(): ?string
    {
        return $this->filledQuantity;
    }

    /**
     * @param string|null $filledQuantity
     *
     * @return Order
     */
    public function setFilledQuantity(?string $filledQuantity): Order
    {
        $this->filledQuantity = $filledQuantity;

        return $this;
    }
}
