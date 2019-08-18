<?php

namespace App\Bus\MessageHandler\Event\Websocket;

use App\Bus\Message\Event\WebsocketEvent;

interface WebsocketEventHandlerInterface
{
    /**
     * @param WebsocketEvent $event
     */
    public function handle(WebsocketEvent $event): void;

    /**
     * @param WebsocketEvent $event
     *
     * @return bool
     */
    public function supports(WebsocketEvent $event): bool;
}
