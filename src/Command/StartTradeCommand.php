<?php

namespace App\Command;

use App\Bus\Message\Command\CreateExchangeOrdersCommand;
use App\Bus\Message\Query\BuyOrderQuery;
use App\Form\Type\TradeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class StartTradeCommand extends Command
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

    protected function configure()
    {
        $this
            ->addArgument('symbol', InputArgument::REQUIRED, 'symbol to create trade for')
            ->addArgument('price', InputArgument::REQUIRED, 'entry price')
            ->addArgument('quantity', InputArgument::REQUIRED, 'quantity, for BTCUSDT this would be amount of USDT')
            ->addOption(
                'range',
                null,
                InputOption::VALUE_REQUIRED,
                'makes price a range, this is the high range value, <price> is the low range value'
            )
            ->addOption(
                'stoploss',
                's',
                InputOption::VALUE_REQUIRED,
                'quantity, for BTCUSDT this would be amount of USDT'
            )
            ->addOption(
                'takeprofit',
                't',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'profit points, format is <price>:<percentage>, for example 10000:25 for taking 25% at 10k'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $config = [
            'entryLow' => $input->getArgument('price'),
            'symbol' => $input->getArgument('symbol'),
            'quantity' => $input->getArgument('quantity'),
        ];

        if ($value = $input->getOption('range')) {
            $config['rangeHigh'] = $value;
        }

        if ($value = $input->getOption('stoploss')) {
            $config['stoploss'] = $value;
        }

        foreach ($input->getOption('takeprofit') as $takeprofit) {
            [$price, $percentage] = explode(':', $takeprofit);

            $config['takeProfits'] = $config['takeProfits'] ?? [];
            $config['takeProfits'][] = ['price' => $price, 'percentage' => $percentage];
        }

        $table = new Table($output);
        $table->setHeaders(['Setting', 'Value']);
        $table->setRows(array_reduce(array_keys($config), static function (array $rows, $setting) use ($config) {
            $rows[] = [$setting, !is_string($config[$setting]) ? json_encode($config[$setting]) : $config[$setting]];

            return $rows;
        }, []));
        $table->render();

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this trade? [N/y]: ', false);

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $form = $this->formFactory->create(TradeType::class);
        $form->submit($config);

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
