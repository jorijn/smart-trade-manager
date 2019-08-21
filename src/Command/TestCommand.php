<?php

namespace App\Command;

use App\Bus\Message\Command\CreateExchangeOrdersCommand;
use App\Bus\Message\Query\BuyOrderQuery;
use App\Form\Type\TradeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class TestCommand extends Command
{
    use HandleTrait;

    /** @var EntityManagerInterface */
    protected $entityManager;
    /** @var MessageBusInterface */
    protected $commandbus;
    /** @var FormBuilderInterface */
    protected $formFactory;

    /**
     * @param string                 $name
     * @param EntityManagerInterface $entityManager
     * @param MessageBusInterface    $queryBus
     * @param MessageBusInterface    $commandbus
     * @param FormFactoryInterface   $formFactory
     */
    public function __construct(
        string $name,
        EntityManagerInterface $entityManager,
        MessageBusInterface $queryBus,
        MessageBusInterface $commandbus,
        FormFactoryInterface $formFactory
    ) {
        parent::__construct($name);

        $this->messageBus = $queryBus;
        $this->entityManager = $entityManager;
        $this->commandbus = $commandbus;
        $this->formFactory = $formFactory;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $in = [
            'entryLow' => '10500',
            'entryHigh' => '10750',
            'symbol' => 'BTCUSDT',
            'quantity' => 100,
            'stoploss' => ['price' => '7000'],
            'takeProfits' => [
                ['price' => '13000', 'percentage' => '50'],
                ['price' => '14000', 'percentage' => '25'],
                ['price' => '15000', 'percentage' => '25'],
            ],
        ];

        // disclaimer: ugly, this is to test some app logic
        $form = $this->formFactory->create(TradeType::class);
        $form->submit($in);

        if ($form->isSubmitted() && $form->isValid()) {
            $trade = $form->getData();

            $this->entityManager->persist($trade);
            $this->entityManager->flush();

            $this->commandbus->dispatch(
                new CreateExchangeOrdersCommand(
                    ...$this->handle(new BuyOrderQuery($trade->getId()))
                )
            );
        } else {
            foreach ($form->getErrors(true) as $error) {
                $io->error(sprintf('[%s] %s', $error->getCause()->getPropertyPath(), $error->getMessage()));
            }
        }
    }
}
