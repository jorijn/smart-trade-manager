<?php

namespace App\Bus\Message\Query;

use App\Bus\Middleware\CacheInterface;

class SymbolListQuery implements CacheInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExpiresAfter()
    {
        return 86400;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey(): string
    {
        return str_replace('\\', '_', __CLASS__);
    }
}
