<?php

namespace App\Model;

class ExchangeOcoOrder implements ExchangeOrderInterface
{
    /** @var string */
    protected $orderListId;
    /** @var string */
    protected $listStatusType;
    /** @var string */
    protected $listOrderStatus;
    /** @var int */
    protected $listClientOrderId;
    /** @var string */
    protected $stopLimitTimeInForce = 'GTC';
    /** @var string */
    protected $symbol;
    /** @var string */
    protected $side;
    /** @var string */
    protected $limitClientOrderId;
    /** @var string */
    protected $stopClientOrderId;
    /** @var string */
    protected $quantity;
    /** @var string */
    protected $price;
    /** @var string */
    protected $stopPrice;
    /** @var string */
    protected $stopLimitPrice;
    /** @var int */
    protected $recvWindow = 10000;
    /** @var Trade */
    protected $trade;

    public function __construct()
    {
        $this->limitClientOrderId = uniqid('', false);
        $this->stopClientOrderId = uniqid('', false);
    }

    /**
     * @return string
     */
    public function getOrderListId(): ?string
    {
        return $this->orderListId;
    }

    /**
     * @param string $orderListId
     *
     * @return ExchangeOcoOrder
     */
    public function setOrderListId(string $orderListId): ExchangeOcoOrder
    {
        $this->orderListId = $orderListId;

        return $this;
    }

    /**
     * @return string
     */
    public function getListStatusType(): ?string
    {
        return $this->listStatusType;
    }

    /**
     * @param string $listStatusType
     *
     * @return ExchangeOcoOrder
     */
    public function setListStatusType(string $listStatusType): ExchangeOcoOrder
    {
        $this->listStatusType = $listStatusType;

        return $this;
    }

    /**
     * @return string
     */
    public function getListOrderStatus(): ?string
    {
        return $this->listOrderStatus;
    }

    /**
     * @param string $listOrderStatus
     *
     * @return ExchangeOcoOrder
     */
    public function setListOrderStatus(string $listOrderStatus): ExchangeOcoOrder
    {
        $this->listOrderStatus = $listOrderStatus;

        return $this;
    }

    /**
     * @return int
     */
    public function getListClientOrderId(): int
    {
        return $this->listClientOrderId;
    }

    /**
     * @param int $listClientOrderId
     *
     * @return ExchangeOcoOrder
     */
    public function setListClientOrderId(int $listClientOrderId): ExchangeOcoOrder
    {
        $this->listClientOrderId = $listClientOrderId;

        return $this;
    }

    /**
     * @return string
     */
    public function getStopLimitTimeInForce(): ?string
    {
        return $this->stopLimitTimeInForce;
    }

    /**
     * @param string $stopLimitTimeInForce
     *
     * @return ExchangeOcoOrder
     */
    public function setStopLimitTimeInForce(string $stopLimitTimeInForce): ExchangeOcoOrder
    {
        $this->stopLimitTimeInForce = $stopLimitTimeInForce;

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
     * @return ExchangeOcoOrder
     */
    public function setSymbol(string $symbol): ExchangeOcoOrder
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return string
     */
    public function getSide(): ?string
    {
        return $this->side;
    }

    /**
     * @param string $side
     *
     * @return ExchangeOcoOrder
     */
    public function setSide(string $side): ExchangeOcoOrder
    {
        $this->side = $side;

        return $this;
    }

    /**
     * @return string
     */
    public function getLimitClientOrderId(): ?string
    {
        return $this->limitClientOrderId;
    }

    /**
     * @param string $limitClientOrderId
     *
     * @return ExchangeOcoOrder
     */
    public function setLimitClientOrderId(string $limitClientOrderId): ExchangeOcoOrder
    {
        $this->limitClientOrderId = $limitClientOrderId;

        return $this;
    }

    /**
     * @return string
     */
    public function getStopClientOrderId(): ?string
    {
        return $this->stopClientOrderId;
    }

    /**
     * @param string $stopClientOrderId
     *
     * @return ExchangeOcoOrder
     */
    public function setStopClientOrderId(string $stopClientOrderId): ExchangeOcoOrder
    {
        $this->stopClientOrderId = $stopClientOrderId;

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
     * @return ExchangeOcoOrder
     */
    public function setQuantity(string $quantity): ExchangeOcoOrder
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrice(): ?string
    {
        return $this->price;
    }

    /**
     * @param string $price
     *
     * @return ExchangeOcoOrder
     */
    public function setPrice(string $price): ExchangeOcoOrder
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string
     */
    public function getStopPrice(): ?string
    {
        return $this->stopPrice;
    }

    /**
     * @param string $stopPrice
     *
     * @return ExchangeOcoOrder
     */
    public function setStopPrice(string $stopPrice): ExchangeOcoOrder
    {
        $this->stopPrice = $stopPrice;

        return $this;
    }

    /**
     * @return string
     */
    public function getStopLimitPrice(): ?string
    {
        return $this->stopLimitPrice;
    }

    /**
     * @param string $stopLimitPrice
     *
     * @return ExchangeOcoOrder
     */
    public function setStopLimitPrice(string $stopLimitPrice): ExchangeOcoOrder
    {
        $this->stopLimitPrice = $stopLimitPrice;

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
     * @return ExchangeOcoOrder
     */
    public function setRecvWindow(int $recvWindow): ExchangeOcoOrder
    {
        $this->recvWindow = $recvWindow;

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
     */
    public function setTrade(Trade $trade): ExchangeOcoOrder
    {
        $this->trade = $trade;

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
            'quantity' => (string) $this->quantity,
            'price' => (string) $this->price,
            'stopPrice' => (string) $this->stopPrice,
            'newOrderRespType' => 'FULL',
            'stopLimitTimeInForce' => $this->stopLimitTimeInForce,
        ];

//        if ($this->limitClientOrderId) {
//            $attributes['limitClientOrderId'] = $this->limitClientOrderId;
//        }
//
//        if ($this->stopClientOrderId) {
//            $attributes['stopClientOrderId'] = $this->stopClientOrderId;
//        }

        if ($this->stopLimitPrice) {
            $attributes['stopLimitPrice'] = $this->stopLimitPrice;
        }

        if ($this->recvWindow) {
            $attributes['recvWindow'] = $this->recvWindow;
        }

        return $attributes;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return 'v3/order/oco';
    }
}
