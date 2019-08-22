<?php

namespace App\Repository;

use App\Model\ExchangeOrder;
use App\Model\TakeProfit;
use App\Model\Trade;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;

class ExchangeOrderRepository extends EntityRepository
{
    public const ALLOWED_ORDER_STATUS = [
        'NEW',
        'PARTIALLY_FILLED',
        'FILLED',
        'CANCELLED',
    ];

    /**
     * @param int $clientOrderId
     *
     * @return ExchangeOrder|null
     */
    public function findOneByOrderId(int $clientOrderId): ?ExchangeOrder
    {
        return $this->findOneBy(['orderId' => $clientOrderId]);
    }

    /**
     * @param TakeProfit[] $takeProfits
     * @param array        $allowedOrderStatus
     *
     * @return mixed
     */
    public function findActiveSellOrdersByTakeProfits(array $takeProfits, $allowedOrderStatus = self::ALLOWED_ORDER_STATUS)
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->where('o.status IN (:allowedStatus)')
            ->andWhere('o.takeProfit IN (:tpIds)');

        $qb->setParameter('tpIds', array_map(static function (TakeProfit $takeProfit) {
            return $takeProfit->getId();
        }, $takeProfits), Connection::PARAM_INT_ARRAY);

        $qb->setParameter('allowedStatus', $allowedOrderStatus, Connection::PARAM_STR_ARRAY);

        return $qb->getQuery()->execute();
    }

    /**
     * @param Trade $trade
     *
     * @return mixed
     */
    public function findBuyOrders(Trade $trade)
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->where('o.status IN (:allowedStatus)')
            ->andWhere('o.takeProfit IS NULL')
            ->andWhere('o.stoploss IS NULL')
            ->andWhere('o.trade = :trade');

        $qb->setParameter('allowedStatus', self::ALLOWED_ORDER_STATUS, Connection::PARAM_STR_ARRAY);
        $qb->setParameter('trade', $trade);

        return $qb->getQuery()->execute();
    }

    /**
     * @param Trade $trade
     *
     * @return mixed
     */
    public function findStopLossOrders(Trade $trade)
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->where('o.status IN (:allowedStatus)')
            ->andWhere('o.takeProfit IS NULL')
            ->andWhere('o.stoploss IS NOT NULL')
            ->andWhere('o.trade = :trade');

        $qb->setParameter('allowedStatus', self::ALLOWED_ORDER_STATUS, Connection::PARAM_STR_ARRAY);
        $qb->setParameter('trade', $trade);

        return $qb->getQuery()->execute();
    }

    /**
     * @param Trade $trade
     *
     * @return mixed
     */
    public function findTakeProfitOrders(Trade $trade)
    {
        $qb = $this->createQueryBuilder('o');
        $qb
            ->where('o.status IN (:allowedStatus)')
            ->andWhere('o.takeProfit IS NOT NULL')
            ->andWhere('o.stoploss IS NULL')
            ->andWhere('o.trade = :trade');

        $qb->setParameter('allowedStatus', self::ALLOWED_ORDER_STATUS, Connection::PARAM_STR_ARRAY);
        $qb->setParameter('trade', $trade);

        return $qb->getQuery()->execute();
    }

    /**
     * @return array|Collection
     */
    public function getSymbolsWithPendingOrders()
    {
        $qb = $this->createQueryBuilder('eo');
        $qb
            ->select('MIN(eo.orderId) AS oldest_order', 'eo.symbol')
            ->where('eo.status IN (:status)')
            ->groupBy('eo.symbol');

        $qb->setParameter('status', ['NEW', 'PARTIALLY_FILLED'], Connection::PARAM_STR_ARRAY);

        return $qb->getQuery()->execute();
    }
}
