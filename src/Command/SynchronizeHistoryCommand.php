<?php

namespace App\Command;

use App\Bus\Message\Command\SynchronizeOrderHistoryCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SynchronizeHistoryCommand extends Command
{
    /** @var MessageBusInterface */
    protected $commandBus;

    /**
     * @param string              $name
     * @param MessageBusInterface $commandBus
     */
    public function __construct(string $name, MessageBusInterface $commandBus)
    {
        parent::__construct($name);
        $this->commandBus = $commandBus;
    }

    protected function configure()
    {
        $this
            ->addOption('with-events', null, InputOption::VALUE_NONE, 'Trigger order events')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->commandBus->dispatch(new SynchronizeOrderHistoryCommand((bool) $input->getOption('with-events')));
    }
}
