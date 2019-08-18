<?php

namespace App\Bus\MessageHandler\Event;

use App\Bus\Message\Event\WebsocketEvent;
use App\Bus\MessageHandler\Event\Websocket\WebsocketEventHandlerInterface;

class WebsocketEventHandler
{
    /** @var WebsocketEventHandlerInterface[]|iterable */
    protected $handlers;

    /**
     * @param iterable|WebsocketEventHandlerInterface[] $handlers
     */
    public function __construct(iterable $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * @param WebsocketEvent $event
     */
    public function __invoke(WebsocketEvent $event)
    {
        foreach ($this->handlers as $eventHandler) {
            if ($eventHandler->supports($event)) {
                $eventHandler->handle($event);
            }
        }
    }
}
