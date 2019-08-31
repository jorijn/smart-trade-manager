<?php

namespace App\Bus\Middleware;

use App\Bus\Stamp\CacheStamp;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\LogicException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class CacheMiddleware implements MiddlewareInterface
{
    /** @var AdapterInterface */
    protected $cache;

    /**
     * @param AdapterInterface $cache
     */
    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (!$envelope->getMessage() instanceof CacheInterface) {
            return $stack->next()->handle($envelope, $stack);
        }

        /** @var CacheInterface $message */
        $message = $envelope->getMessage();
        $cacheItem = $this->cache->getItem($message->getCacheKey());

        if ($cacheItem->isHit()) {
            return $envelope->with(
                new CacheStamp(CacheStamp::CACHE_HIT),
                new HandledStamp($cacheItem->get(), __CLASS__)
            );
        }

        $envelope = $stack->next()->handle($envelope, $stack);
        $handledStamps = $envelope->all(HandledStamp::class);

        if (!$handledStamps) {
            throw new LogicException(sprintf(
                'Message of type "%s" was handled zero times. Exactly one handler is expected when using "%s::%s()".',
                get_class($envelope->getMessage()),
                get_class($this),
                __FUNCTION__
            ));
        }

        if (count($handledStamps) > 1) {
            $handlers = implode(', ', array_map(static function (HandledStamp $stamp): string {
                return sprintf('"%s"', $stamp->getHandlerName());
            }, $handledStamps));

            throw new LogicException(sprintf(
                'Message of type "%s" was handled multiple times. Only one handler is expected when using "%s::%s()", got %d: %s.',
                get_class($envelope->getMessage()),
                get_class($this),
                __FUNCTION__,
                count($handledStamps),
                $handlers
            ));
        }

        $cacheItem->set($handledStamps[0]->getResult());
        $cacheItem->expiresAfter($message->getExpiresAfter());

        $this->cache->save($cacheItem);
        $this->cache->commit();

        return $envelope->with(new CacheStamp(CacheStamp::CACHE_MISS));
    }
}
