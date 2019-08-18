<?php

namespace App\Bus\Message\Event;

use App\Bus\Message\AsyncMessageInterface;

class WebsocketEvent implements AsyncMessageInterface
{
    /** @var string */
    protected $type;
    /** @var array */
    protected $payload;

    /**
     * @param string $type
     * @param array  $payload
     */
    public function __construct(string $type, array $payload = [])
    {
        $this->type = $type;
        $this->payload = $payload;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
