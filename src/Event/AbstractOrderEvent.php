<?php

namespace App\Event;

use App\Model\ExchangeOrderInterface;

abstract class AbstractOrderEvent
{
    /** @var ExchangeOrderInterface */
    protected $order;

    /**
     * @param ExchangeOrderInterface $order
     */
    public function __construct(ExchangeOrderInterface $order)
    {
        $this->order = $order;
    }

    /**
     * @return ExchangeOrderInterface
     */
    public function getOrder(): ExchangeOrderInterface
    {
        return $this->order;
    }
}
