<?php

namespace App\Bus\Message;

class StartBuyingForNewTradeCommand
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

    /**
     * @param int $tradeId
     */
    public function setTradeId(int $tradeId): void
    {
        $this->tradeId = $tradeId;
    }
}
