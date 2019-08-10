<?php

namespace App\Model;

class Trade
{
    /** @var int */
    protected $id;
    /** @var string */
    protected $symbol;
    /** @var string */
    protected $quantity;
    /** @var StopLoss|null */
    protected $stoploss;
    /** @var TakeProfit[] */
    protected $takeProfits;
}
