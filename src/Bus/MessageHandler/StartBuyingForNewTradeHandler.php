<?php

namespace App\Bus\MessageHandler;

use App\Bus\Message\StartBuyingForNewTradeCommand;
use App\Exception\SymbolNotFoundException;
use App\Exception\TradeNotFoundException;
use App\Model\Order;
use App\Model\Symbol;
use App\Model\Trade;
use App\Repository\OrderRepository;
use App\Repository\SymbolRepository;
use App\Repository\TradeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHP\Math\BigNumber\BigNumber;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StartBuyingForNewTradeHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    protected $manager;
    /** @var OrderRepository|ObjectRepository */
    protected $orderRepository;
    /** @var TradeRepository|ObjectRepository */
    protected $tradeRepository;
    /** @var int */
    protected $ladderSteps;
    /** @var SymbolRepository|ObjectRepository */
    protected $symbolRepository;
    /** @var HttpClientInterface */
    protected $binanceApiClient;

    /**
     * @param ObjectManager $manager
     * @param int           $ladderSteps
     */
    public function __construct(ObjectManager $manager, HttpClientInterface $binanceApiClient, int $ladderSteps = 10)
    {
        $this->manager = $manager;
        $this->orderRepository = $manager->getRepository(Order::class);
        $this->tradeRepository = $manager->getRepository(Trade::class);
        $this->symbolRepository = $manager->getRepository(Symbol::class);

        // TODO make this configurable
        $this->ladderSteps = $ladderSteps;
        $this->binanceApiClient = $binanceApiClient;
    }

    public function __invoke(StartBuyingForNewTradeCommand $command)
    {
        $trade = $this->tradeRepository->find($command->getTradeId());
        if (!$trade instanceof Trade) {
            throw new TradeNotFoundException(sprintf('trade with ID %s not found', $command->getTradeId()));
        }

        $this->getBuyOrdersForLadderLimit($trade);
    }

    protected function getBuyOrdersForLadderLimit(Trade $trade)
    {
        $symbol = $this->symbolRepository->find($trade->getSymbol());
        if (!$symbol instanceof Symbol) {
            throw new SymbolNotFoundException(sprintf('symbol with name %s was not found', $trade->getSymbol()));
        }

        $low = new BigNumber($trade->getEntryLow(), $symbol->getQuotePrecision(), false);
        $high = new BigNumber($trade->getEntryHigh(), $symbol->getQuotePrecision(), false);
        $difference = $high->subtract($low);
        $step = $difference->divide($this->ladderSteps);
        $quantityStep = (new BigNumber($trade->getQuantity()))->divide($this->ladderSteps);

        // TODO make prettier
        $orders = [];
        foreach (range(1, $this->ladderSteps) as $orderNumber) {
            $order = new Order();
            $order->setSymbol($symbol->getSymbol());
            $order->setPrice($low->add($step->multiply($orderNumber)));
            $order->setSide(Order::BUY);
            $order->setType(Order::LIMIT);
            $order->setQuantity($quantityStep);

            $orders[] = $order;
        }

        /**
         * TODO: split it up smartly, take lot size into account, put the weight on the coin (0.1/10 instead of quantity/10)
         * @see https://github.com/binance-exchange/binance-official-api-docs/blob/master/rest-api.md#lot_size
         */

        foreach ($orders as $order) {
            $response = $this->binanceApiClient->request('POST', 'v3/order', [
                'extra' => ['security_type' => 'TRADE'],
                'query' => $order->toApiAttributes()
            ]);
        }
    }

    protected function getBuyOrdersForSingleLimit(Trade $trade)
    {
        // TODO extract this logic
    }
}
