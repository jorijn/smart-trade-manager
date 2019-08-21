<?php

namespace App\Command;

use App\Bus\Message\Command\EvaluatePositionsCommand;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EvaluatePositionsForceCommand extends Command
{
    /** @var CacheItemPoolInterface */
    protected $pool;
    /** @var MessageBusInterface */
    protected $commandBus;

    /**
     * @param string                 $name
     * @param MessageBusInterface    $commandBus
     * @param CacheItemPoolInterface $pool
     */
    public function __construct(string $name, MessageBusInterface $commandBus, CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
        $this->commandBus = $commandBus;

        parent::__construct($name);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Psr\Cache\InvalidArgumentException
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = new EvaluatePositionsCommand();

        // save latest debounce key, purpose of this piece of code is to check in the handler if it's the latest,
        // otherwise it should discard the evaluation request since a newer one is coming.
        $item = $this->pool->getItem(str_replace('\\', '_', get_class($command)));
        $item->set($command->getKey());

        $this->pool->save($item);

        // dispatch it to the queue and set it to be executed now
        $this->commandBus->dispatch($command);
    }
}
