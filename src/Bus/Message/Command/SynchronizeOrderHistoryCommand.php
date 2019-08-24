<?php

namespace App\Bus\Message\Command;

use App\Bus\Message\AsyncMessageInterface;

class SynchronizeOrderHistoryCommand implements AsyncMessageInterface
{
    /** @var bool */
    protected $triggerEvents;

    /**
     * @param bool $triggerEvents
     */
    public function __construct(bool $triggerEvents = true)
    {
        $this->triggerEvents = $triggerEvents;
    }

    /**
     * @return bool
     */
    public function shouldTriggerEvents(): bool
    {
        return $this->triggerEvents;
    }
}
