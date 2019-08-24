<?php

namespace App\Bus\Message\Command;

use App\Model\ExchangeOrderInterface;

abstract class AbstractOrdersCommand
{
    /** @var ExchangeOrderInterface[] */
    protected $orders = [];

    /**
     * @param ExchangeOrderInterface ...$orders
     */
    public function __construct(...$orders)
    {
        $this->orders = $orders;
    }

    /**
     * @return ExchangeOrderInterface[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }
}
