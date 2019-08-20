<?php

namespace App\Bus\Message\Command;

use App\Bus\Message\AsyncMessageInterface;

class EvaluatePositionsCommand implements AsyncMessageInterface
{
    /** @var string */
    protected $key;

    public function __construct()
    {
        $this->key = uniqid('key_', true);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
