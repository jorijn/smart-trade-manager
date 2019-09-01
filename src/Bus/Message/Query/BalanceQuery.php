<?php

namespace App\Bus\Message\Query;

use App\Bus\Middleware\CacheInterface;

class BalanceQuery implements CacheInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExpiresAfter()
    {
        return 900;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey(): string
    {
        return str_replace('\\', '_', __CLASS__);
    }
}
