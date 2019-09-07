<?php

namespace App\EventListener;

use App\Bus\Message\Command\EvaluatePositionsCommand;
use App\Model\ExchangeOrderInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class OrderChangedEventListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var CacheItemPoolInterface */
    protected $pool;
    /** @var MessageBusInterface */
    protected $commandBus;

    /**
     * @param MessageBusInterface    $commandBus
     * @param CacheItemPoolInterface $pool
     * @param LoggerInterface        $logger
     */
    public function __construct(MessageBusInterface $commandBus, CacheItemPoolInterface $pool, LoggerInterface $logger)
    {
        $this->pool = $pool;
        $this->commandBus = $commandBus;

        $this->setLogger($logger);
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof ExchangeOrderInterface) {
            return;
        }

        $command = new EvaluatePositionsCommand();

        // save latest debounce key, purpose of this piece of code is to check in the handler if it's the latest,
        // otherwise it should discard the evaluation request since a newer one is coming.
        $item = $this->pool->getItem(str_replace('\\', '_', get_class($command)));
        $item->set($command->getKey());

        $this->pool->save($item);

        $this->logger->info(
            'order update event received, triggering evaluation of positions',
            [$entity->getAttributeIdentifier() => $entity->getAttributeIdentifierValue()]
        );

        // dispatch it to the queue and set it to be executed in 10 seconds
        $this->commandBus->dispatch($command, [
            new DelayStamp(10000),
        ]);
    }
}
