<?php

namespace App\Event;

use App\Model\ExchangeOrder;

abstract class AbstractOrderEvent
{
    /** @var ExchangeOrder */
    protected $order;

    /**
     * @param ExchangeOrder $order
     */
    public function __construct(ExchangeOrder $order)
    {
        $this->order = $order;
    }

    /**
     * @return ExchangeOrder
     */
    public function getOrder(): ExchangeOrder
    {
        return $this->order;
    }
}
