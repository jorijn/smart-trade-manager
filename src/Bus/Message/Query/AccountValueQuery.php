<?php

namespace App\Bus\Message\Query;

use App\Bus\Middleware\CacheInterface;

class AccountValueQuery implements CacheInterface
{
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
