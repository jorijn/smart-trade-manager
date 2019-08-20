<?php

namespace App\Bus\Message\Command;

use App\Model\ExchangeOrder;

class CreateExchangeOrdersCommand
{
    /** @var ExchangeOrder[] */
    protected $orders = [];

    /**
     * @param ExchangeOrder ...$orders
     */
    public function __construct(...$orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return ExchangeOrder[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }
}
