<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ExchangeOcoOrder implements ExchangeOrderInterface, \JsonSerializable
{
    /** @var array */
    protected const UPDATE_MAP = [
        'executedQty' => 'setFilledQuantity',
        'cummulativeQuoteQty' => 'setFilledQuoteQuantity',
        'status' => 'setStatus',
        'orderId' => 'setOrderId',
        'transactTime' => 'setUpdatedAt',
        'origQty' => 'setQuantity',
        'orderReports' => 'setOrderReports',
        'orderListId' => 'setOrderListId',
        'listStatusType' => 'setListStatusType',
        'listOrderStatus' => 'setListOrderStatus',
        'transactionTime' => 'setUpdatedAt',
    ];

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
    /** @var int */
    protected $updatedAt;
    /** @var TakeProfit */
    protected $takeProfit;
    /** @var ExchangeOrder[] */
    protected $orders;

    public function __construct(array $data = [])
    {
        $this->orders = new ArrayCollection();

        $this->update($data);
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

    /**
     * @param array $orderReports
     */
    public function setOrderReports(array $orderReports)
    {
        foreach ($orderReports as $orderReport) {
            $order = new ExchangeOrder();
            $order->setOrderList($this);
            $order->setTrade($this->getTrade());
            $order->setTakeProfit($this->getTakeProfit());
            $order->update($orderReport);

            $this->orders->add($order);
        }
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
     * @return TakeProfit
     */
    public function getTakeProfit(): ?TakeProfit
    {
        return $this->takeProfit;
    }

    /**
     * @param TakeProfit $takeProfit
     *
     * @return ExchangeOcoOrder
     */
    public function setTakeProfit(TakeProfit $takeProfit): ExchangeOcoOrder
    {
        $this->takeProfit = $takeProfit;

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
     * @return ExchangeOcoOrder
     */
    public function setUpdatedAt(int $updatedAt): ExchangeOcoOrder
    {
        $this->updatedAt = $updatedAt;

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
    public function getSymbol(): string
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

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array_map(static function ($value) {
            if ($value instanceof Collection) {
                return $value->toArray();
            }

            return $value;
        }, array_diff_key(get_object_vars($this), array_flip(['trade', 'takeProfit'])));
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeIdentifier(): string
    {
        return 'orderListId';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeIdentifierValue(): ?string
    {
        return $this->getOrderListId();
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
}
