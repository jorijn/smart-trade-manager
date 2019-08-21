<?php

namespace App\EventListener;

use App\Bus\Message\Command\EvaluatePositionsCommand;
use App\Event\AbstractOrderEvent;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class OrderChangedEventListener
{
    /** @var CacheItemPoolInterface */
    protected $pool;
    /** @var MessageBusInterface */
    protected $commandBus;

    /**
     * @param MessageBusInterface    $commandBus
     * @param CacheItemPoolInterface $pool
     */
    public function __construct(MessageBusInterface $commandBus, CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
        $this->commandBus = $commandBus;
    }

    /**
     * @param AbstractOrderEvent $event
     *
     * @throws InvalidArgumentException
     */
    public function onOrderChanged(AbstractOrderEvent $event): void
    {
        $command = new EvaluatePositionsCommand();

        // save latest debounce key, purpose of this piece of code is to check in the handler if it's the latest,
        // otherwise it should discard the evaluation request since a newer one is coming.
        $item = $this->pool->getItem(str_replace('\\', '_', get_class($command)));
        $item->set($command->getKey());

        $this->pool->save($item);

        // dispatch it to the queue and set it to be executed in 5 seconds
        $this->commandBus->dispatch($command, [
            new DelayStamp(5000),
        ]);
    }
}
