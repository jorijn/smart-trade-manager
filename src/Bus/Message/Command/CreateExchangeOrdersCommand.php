<?php

namespace App\Bus\Message\Command;

use App\Model\Order;

class CreateExchangeOrdersCommand
{
    /** @var Order[] */
    protected $orders = [];

    /**
     * @param Order ...$orders
     */
    public function __construct(...$orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return Order[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }
}
