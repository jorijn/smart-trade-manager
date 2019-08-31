<?php

namespace App\Bus\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class CacheStamp implements StampInterface
{
    public const CACHE_HIT = 1;
    public const CACHE_MISS = 0;

    /** @var int */
    protected $cacheStatus;

    /**
     * @param int $cacheStatus
     */
    public function __construct(int $cacheStatus)
    {
        $this->cacheStatus = $cacheStatus;
    }
}
