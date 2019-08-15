<?php

namespace App\Command;

use PHP\Math\BigNumber\BigNumber;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TradeSendOcoCommand extends Command
{
    protected static $defaultName = 'app:trade:send-oco';
    /** @var HttpClientInterface */
    protected $binanceApiClient;

    public function __construct(string $name = null, HttpClientInterface $binanceApiClient)
    {
        parent::__construct($name);
        $this->binanceApiClient = $binanceApiClient;
    }

    protected function configure()
    {
        $this
            ->setDescription('Sends an OCO order, for debugging purposes')
            ->addOption('quantity', null, InputOption::VALUE_REQUIRED, 'Quantity of order')
            ->addOption('symbol', null, InputOption::VALUE_REQUIRED, 'Symbol of order, BTCUSDT for example or BTCETH')
            ->addOption('stoploss', null, InputOption::VALUE_REQUIRED, 'Price for Stop-Loss stop order')
            ->addOption('stoplosslimit', null, InputOption::VALUE_REQUIRED, 'Price for Stop-Loss limit order')
            ->addOption('takeprofit', null, InputOption::VALUE_REQUIRED, 'Price for Take-Profit order')
            ->addOption('side', null, InputOption::VALUE_REQUIRED, 'Price for Take-Profit order', 'SELL');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = $this->binanceApiClient->request('POST', 'v3/order/oco', [
            'extra' => ['security_type' => 'TRADE'],
            'body' => [
                'symbol' => $input->getOption('symbol'),
                'side' => $input->getOption('side'),
                'quantity' => $input->getOption('quantity'),
                'price' => $input->getOption('takeprofit'),
                'stopPrice' => $input->getOption('stoploss'),
                'stopLimitPrice' => $input->getOption('stoplosslimit'),
                'stopLimitTimeInForce' => 'GTC',
                'newOrderRespType' => 'FULL',
                'recvWindow' => 10000,
            ],
        ]);

        // TODO implement rounding to tick size
        // @see https://github.com/jaggedsoft/node-binance-api/blob/master/node-binance-api.js#L881
        dump(json_decode($response->getContent()));
    }
}
