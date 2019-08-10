<?php

namespace App\Model;

class TakeProfit
{
    /** @var int */
    const AMOUNT_TYPE_FIXED_QUANTITY = 1;
    /** @var int */
    const AMOUNT_TYPE_TRADE_PERCENTAGE = 2;

    /** @var int */
    protected $id;
    /** @var string|null */
    protected $quantity;
    /** @var int */
    protected $percentage;
    /** @var int */
    protected $amountType;
    /** @var Trade */
    protected $trade;
}
