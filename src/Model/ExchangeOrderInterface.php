<?php

namespace App\Model;

interface ExchangeOrderInterface
{
    /**
     * @return array
     */
    public function toApiAttributes(): array;

    /**
     * @return string
     */
    public function getEndpoint(): string;
}
