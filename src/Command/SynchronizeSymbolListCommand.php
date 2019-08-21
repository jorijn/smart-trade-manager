<?php

namespace App\Command;

use App\Bus\Message\Query\SymbolListQuery;
use App\Model\Symbol;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class SynchronizeSymbolListCommand extends Command
{
    use HandleTrait;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * @param string                 $name
     * @param MessageBusInterface    $queryBus
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        string $name,
        MessageBusInterface $queryBus,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($name);

        $this->messageBus = $queryBus;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setDescription('Synchronized the symbol list for the Binance exchange');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        // clear the symbol list
        $repository = $this->entityManager->getRepository(Symbol::class);
        $repository->deleteAll();

        /** @var Symbol[] $apiSymbols */
        $apiSymbols = $this->handle(new SymbolListQuery());

        // merge & persist all symbols to the ORM, flag symbol as processed
        foreach ($apiSymbols as $symbol) {
            $this->entityManager->persist($symbol);
            $processed[] = $symbol->getSymbol();
        }

        // flush changes to the database
        $this->entityManager->flush();

        $io->success(sprintf('processed %s symbols.', count($apiSymbols)));
    }
}
