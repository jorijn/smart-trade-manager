<?php

namespace App\Bus\Message\Query;

use App\Bus\Middleware\CacheInterface;

class SymbolPriceQuery implements CacheInterface
{
    /** @var string|null */
    protected $symbol;

    /**
     * @return string|null
     */
    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    /**
     * {@inheritDoc}
     */
    public function getExpiresAfter()
    {
        return 900;
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheKey(): string
    {
        return str_replace('\\', '_', __CLASS__);
    }
}
