<?php

namespace App\Command;

use App\Component\ExchangePriceFormatter;
use App\Model\Symbol;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TradeSendOcoCommand extends Command
{
    protected static $defaultName = 'app:trade:send-oco';
    /** @var HttpClientInterface */
    protected $binanceApiClient;
    /** @var ObjectManager */
    protected $manager;
    /** @var ExchangePriceFormatter */
    protected $formatter;

    public function __construct(
        string $name = null,
        HttpClientInterface $binanceApiClient,
        ObjectManager $manager,
        ExchangePriceFormatter $formatter
    ) {
        parent::__construct($name);
        $this->binanceApiClient = $binanceApiClient;
        $this->manager = $manager;
        $this->formatter = $formatter;
    }

    protected function configure()
    {
        $this
            ->setDescription('Sends an OCO order, for debugging purposes')
            ->addOption('quantity', null, InputOption::VALUE_REQUIRED, 'Quantity of order')
            ->addOption('symbol', null, InputOption::VALUE_REQUIRED, 'Symbol of order, BTCUSDT for example or BTCETH')
            ->addOption('stoploss', null, InputOption::VALUE_REQUIRED, 'Price for Stop-Loss stop order')
            ->addOption('takeprofit', null, InputOption::VALUE_REQUIRED, 'Price for Take-Profit order');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $symbol = $this->manager->find(Symbol::class, $input->getOption('symbol'));
        if ($symbol === null) {
            $io->error('symbol not found');

            return 1;
        }

        $quantity = $this->formatter->roundStep($symbol, $input->getOption('quantity'));
        $price = $this->formatter->roundTicks($symbol, $input->getOption('takeprofit'));
        $stop = $this->formatter->roundTicks($symbol, $input->getOption('stoploss'));
        $stopLimit = $this->formatter->roundTicks(
            $symbol,
            bcdiv($stop, '1.01', $this->formatter->getPriceScale($symbol))
        );

        $response = $this->binanceApiClient->request('POST', 'v3/order/oco', [
            'extra' => ['security_type' => 'TRADE'],
            'body' => [
                'symbol' => $symbol->getSymbol(),
                'side' => 'SELL',
                'quantity' => $quantity,
                'price' => $price,
                'stopPrice' => $stop,
                'stopLimitPrice' => $stopLimit,
                'stopLimitTimeInForce' => 'GTC',
                'newOrderRespType' => 'FULL',
                'recvWindow' => 10000,
            ],
        ]);
    }
}
