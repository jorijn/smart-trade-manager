<?php

namespace App\Bus\MessageHandler\Query;

use App\Bus\Message\Query\AbstractOrderQuery;
use App\Bus\Message\Query\BuyOrderQuery;
use App\Exception\TradeNotFoundException;
use App\Exception\UnsupportedTradeException;
use App\Model\ExchangeOrder;
use App\Model\Trade;
use App\OrderGenerator\OrderGeneratorInterface;
use App\Repository\TradeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class OrderQueryHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ObjectManager */
    protected $manager;
    /** @var OrderGeneratorInterface[] */
    protected $orderGenerators;
    /** @var TradeRepository|ObjectRepository */
    protected $tradeRepository;

    /**
     * @param ObjectManager                      $manager
     * @param OrderGeneratorInterface[]|iterable $orderGenerators
     * @param LoggerInterface                    $logger
     */
    public function __construct(ObjectManager $manager, iterable $orderGenerators, LoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->tradeRepository = $this->manager->getRepository(Trade::class);
        $this->orderGenerators = $orderGenerators;

        $this->setLogger($logger);
    }

    /**
     * @param BuyOrderQuery $command
     *
     * @return ExchangeOrder[]
     */
    public function __invoke(AbstractOrderQuery $command): array
    {
        $tradeId = $command->getTradeId();
        $trade = $this->tradeRepository->find($tradeId);
        if (!$trade instanceof Trade) {
            $this->logger->error('unable to generate orders, trade not found', ['trade_id' => $tradeId]);

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
            'generators' => array_map(static function (OrderGeneratorInterface $generator) {
                return get_class($generator);
            }, iterator_to_array($this->orderGenerators)),
        ]);

        throw new UnsupportedTradeException('no suitable generator found for order');
    }
}
