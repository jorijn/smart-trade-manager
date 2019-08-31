<?php

namespace App\Bus\Middleware;

interface CacheInterface
{
    /**
     * Gets the expiration time for this cache item.
     *
     * @return int|\DateInterval|null
     *                                The period of time from the present after which the item MUST be considered
     *                                expired. An integer parameter is understood to be the time in seconds until
     *                                expiration. If null is passed explicitly, a default value MAY be used.
     *                                If none is set, the value should be stored permanently or for as long as the
     *                                implementation allows.
     */
    public function getExpiresAfter();

    /**
     * Should return the key under which the result is stored in the cache pool.
     *
     * @return string
     */
    public function getCacheKey(): string;
}
