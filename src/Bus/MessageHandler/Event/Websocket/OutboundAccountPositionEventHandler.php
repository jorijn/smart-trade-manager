<?php

namespace App\Bus\MessageHandler\Event\Websocket;

use App\Bus\Message\Event\WebsocketEvent;

class OutboundAccountPositionEventHandler implements WebsocketEventHandlerInterface
{
    /**
     * @param WebsocketEvent $event
     */
    public function handle(WebsocketEvent $event): void
    {
        // example, implement further
    }

    /**
     * @param WebsocketEvent $event
     *
     * @return bool
     */
    public function supports(WebsocketEvent $event): bool
    {
        return $event->getType() === 'outboundAccountPosition';
    }
}
