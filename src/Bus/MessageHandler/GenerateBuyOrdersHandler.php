<?php

namespace App\Bus\MessageHandler;

use App\Bus\Message\GenerateBuyOrdersCommand;
use App\Exception\TradeNotFoundException;
use App\Exception\UnsupportedTradeException;
use App\Model\Order;
use App\Model\Trade;
use App\OrderGenerator\BuyOrderGeneratorInterface;
use App\Repository\TradeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class GenerateBuyOrdersHandler implements MessageHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ObjectManager */
    protected $manager;
    /** @var BuyOrderGeneratorInterface[] */
    protected $orderGenerators;
    /** @var TradeRepository|ObjectRepository */
    protected $tradeRepository;

    /**
     * @param ObjectManager                         $manager
     * @param BuyOrderGeneratorInterface[]|iterable $orderGenerators
     * @param LoggerInterface                       $logger
     */
    public function __construct(ObjectManager $manager, iterable $orderGenerators, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->tradeRepository = $this->manager->getRepository(Trade::class);
        $this->orderGenerators = $orderGenerators;

        $this->setLogger($logger);
    }

    /**
     * @param GenerateBuyOrdersCommand $command
     *
     * @return Order[]
     */
    public function __invoke(GenerateBuyOrdersCommand $command): array
    {
        $tradeId = $command->getTradeId();
        $trade = $this->tradeRepository->find($tradeId);
        if (!$trade instanceof Trade) {
            $this->logger->error('unable to generate buy orders, trade not found', ['trade_id' => $tradeId]);

            throw new TradeNotFoundException(sprintf('trade with ID %s not found', $tradeId));
        }

        foreach ($this->orderGenerators as $orderGenerator) {
            if ($orderGenerator->supports($trade)) {
                return $orderGenerator->generate($trade);
            }
        }

        $this->logger->critical('no suitable generator found for buy order', [
            'trade_id' => $tradeId,
            'trade' => $trade,
            'generators' => array_map(static function (BuyOrderGeneratorInterface $generator) {
                return get_class($generator);
            }, iterator_to_array($this->orderGenerators)),
        ]);

        throw new UnsupportedTradeException('no suitable generator found for buy order');
    }
}
