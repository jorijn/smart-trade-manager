<?php

namespace App\Model;

use Doctrine\Common\Collections\Collection;

class ExchangeOrder implements ExchangeOrderInterface, \JsonSerializable
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

    /** @var array */
    protected const UPDATE_MAP = [
        'executedQty' => 'setFilledQuantity',
        'cummulativeQuoteQty' => 'setFilledQuoteQuantity',
        'status' => 'setStatus',
        'orderId' => 'setOrderId',
        'transactTime' => 'setUpdatedAt',
        'updateTime' => 'setUpdatedAt',
        'origQty' => 'setQuantity',
        'symbol' => 'setSymbol',
        'price' => 'setPrice',
        'timeInForce' => 'setTimeInForce',
        'type' => 'setType',
        'side' => 'setSide',
        'stopPrice' => 'setStopPrice',
    ];

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
    /** @var int|null */
    protected $orderId;
    /** @var string|null */
    protected $status;
    /** @var string|null */
    protected $filledQuantity;
    /** @var int */
    protected $updatedAt;
    /** @var string|null */
    protected $filledQuoteQuantity;
    /** @var Trade */
    protected $trade;
    /** @var TakeProfit|null */
    protected $takeProfit;
    /** @var int */
    protected $orderList;

    /**
     * @return int
     */
    public function getOrderList(): ?ExchangeOcoOrder
    {
        return $this->orderList;
    }

    /**
     * @param ExchangeOcoOrder $orderList
     *
     * @return ExchangeOrder
     */
    public function setOrderList(ExchangeOcoOrder $orderList): ExchangeOrder
    {
        $this->orderList = $orderList;

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
     * @return ExchangeOrder
     */
    public function setTrade(Trade $trade): ExchangeOrder
    {
        $this->trade = $trade;

        return $this;
    }

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
     * @return ExchangeOrder
     */
    public function setFilledQuoteQuantity(?string $filledQuoteQuantity): ExchangeOrder
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
     * @return ExchangeOrder
     */
    public function setUpdatedAt(int $updatedAt): ExchangeOrder
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
     * @return ExchangeOrder
     */
    public function setSymbol(string $symbol): ExchangeOrder
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
     * @return ExchangeOrder
     */
    public function setSide(string $side): ExchangeOrder
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
     * @return ExchangeOrder
     */
    public function setType(string $type): ExchangeOrder
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
     * @return ExchangeOrder
     */
    public function setTimeInForce(?string $timeInForce): ExchangeOrder
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
     * @return ExchangeOrder
     */
    public function setQuantity(string $quantity): ExchangeOrder
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
     * @return ExchangeOrder
     */
    public function setPrice(string $price): ExchangeOrder
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
     * @return ExchangeOrder
     */
    public function setStopPrice(?string $stopPrice): ExchangeOrder
    {
        if ($stopPrice === '0.00000000') {
            // 0.00000000 means empty
            return $this;
        }

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
     * @return ExchangeOrder
     */
    public function setIcebergQty(?string $icebergQty): ExchangeOrder
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
     * @return ExchangeOrder
     */
    public function setNewOrderRespType(?string $newOrderRespType): ExchangeOrder
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
     * @return ExchangeOrder
     */
    public function setRecvWindow(?int $recvWindow): ExchangeOrder
    {
        $this->recvWindow = $recvWindow;

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
     * @return ExchangeOrder
     */
    public function setStatus(?string $status): ExchangeOrder
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
     * @return ExchangeOrder
     */
    public function setFilledQuantity(?string $filledQuantity): ExchangeOrder
    {
        $this->filledQuantity = $filledQuantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return 'v3/order';
    }

    /**
     * @return TakeProfit|null
     */
    public function getTakeProfit(): ?TakeProfit
    {
        return $this->takeProfit;
    }

    /**
     * @param TakeProfit|null $takeProfit
     */
    public function setTakeProfit(?TakeProfit $takeProfit): void
    {
        $this->takeProfit = $takeProfit;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $attributes = array_map(static function ($value) {
            if ($value instanceof Collection) {
                return $value->toArray();
            }

            return $value;
        }, array_diff_key(get_object_vars($this), array_flip(['trade', 'takeProfit'])));

        $attributes['type'] = 'unknown';
        if (!empty($this->stopPrice)) {
            $attributes['type'] = 'STOP_LOSS';
        } elseif (!$this->takeProfit instanceof TakeProfit) {
            $attributes['type'] = 'BUY';
        } else {
            $attributes['type'] = 'TAKE_PROFIT';
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeIdentifier(): string
    {
        return 'orderId';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeIdentifierValue(): ?string
    {
        return $this->getOrderId();
    }

    /**
     * @return string|null
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string|null $orderId
     *
     * @return ExchangeOrder
     */
    public function setOrderId(string $orderId): ExchangeOrder
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $data): void
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, self::UPDATE_MAP)) {
                $this->{self::UPDATE_MAP[$key]}($value);
            }
        }
    }
}
