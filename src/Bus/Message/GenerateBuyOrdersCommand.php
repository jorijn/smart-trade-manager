<?php

namespace App\Bus\Message;

class GenerateBuyOrdersCommand
{
    /** @var int */
    protected $tradeId;

    /**
     * @param int $tradeId
     */
    public function __construct(int $tradeId)
    {
        $this->tradeId = $tradeId;
    }

    /**
     * @return int
     */
    public function getTradeId(): int
    {
        return $this->tradeId;
    }
}
