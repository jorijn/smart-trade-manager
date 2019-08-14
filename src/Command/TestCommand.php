<?php

namespace App\Command;

use App\Bus\Message\StartBuyingForNewTradeCommand;
use App\Model\StopLoss;
use App\Model\TakeProfit;
use App\Model\Trade;
use App\Repository\TradeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class TestCommand extends Command
{
    use HandleTrait;

    protected static $defaultName = 'app:test';
    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(string $name = null, EntityManagerInterface $entityManager, MessageBusInterface $messageBus)
    {
        parent::__construct($name);
        $this->messageBus = $messageBus;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // disclaimer: ugly, this is to test some app logic

        $stoploss = new StopLoss('9000');

        $trade = new Trade();
        $trade->setSymbol('BTCUSDT');
        $trade->setQuantity('0.1');
        $trade->setStoploss($stoploss);

        $trade->setEntryLow('9100');
        $trade->setEntryHigh('10000');

        $stoploss->setTrade($trade);

        $tp1 = new TakeProfit('13000', 50); $tp1->setTrade($trade);
        $tp2 = new TakeProfit('14000', 25); $tp2->setTrade($trade);
        $tp3 = new TakeProfit('15000', 25); $tp3->setTrade($trade);

        $trade->setTakeProfits([$tp1, $tp2, $tp3]);

        $this->entityManager->persist($trade);
        $this->entityManager->flush();

        $this->handle(new StartBuyingForNewTradeCommand($trade->getId()));
    }
}
