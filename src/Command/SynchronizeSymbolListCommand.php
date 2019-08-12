<?php

namespace App\Command;

use App\Bus\Message\GetSymbolListCommand;
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

    /** @var string */
    protected static $defaultName = 'exchange:binance:synchronize-symbol-list';
    /** @var EntityManagerInterface */
    protected $entityManager;

    /**
     * @param string|null            $name
     * @param MessageBusInterface    $messageBus
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        string $name = null,
        MessageBusInterface $messageBus,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($name);

        $this->messageBus = $messageBus;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setDescription('Synchronized the symbol list for the Binance exchange');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $repository = $this->entityManager->getRepository(Symbol::class);
        $existingSymbols = array_keys($repository->associativeFindAll());
        $processed = [];

        /** @var Symbol[] $apiSymbols */
        $apiSymbols = $this->handle(new GetSymbolListCommand());

        // merge & persist all symbols to the ORM, flag symbol as processed
        foreach ($apiSymbols as $symbol) {
            $this->entityManager->merge($symbol);
            $processed[] = $symbol->getSymbol();
        }

        // flush changes to the database
        $this->entityManager->flush();

        // delete all symbols that are not processed (longer present on the server)
        $repository->removeSymbols(...array_diff($existingSymbols, $processed));

        $io->success(sprintf('processed %s symbols.', count($apiSymbols)));
    }
}
