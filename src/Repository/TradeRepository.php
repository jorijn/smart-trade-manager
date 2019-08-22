<?php

namespace App\Repository;

use App\Model\Trade;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class TradeRepository extends EntityRepository
{
    /**
     * @return Trade[]
     */
    public function getPendingTrades(): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb
            ->leftJoin('t.orders', 'o', Join::WITH, 'o.status IN (:allowedOrderTypes)')
            ->where('t.active = 1')
            ->andWhere('o IS NOT NULL')
            ->groupBy('t.id')
            ->having('SUM(o.filledQuantity) > 0');

        $qb->setParameter('allowedOrderTypes', [
            'NEW',
            'PARTIALLY_FILLED',
            'FILLED',
            'CANCELLED'
        ], Connection::PARAM_STR_ARRAY);

        return $qb->getQuery()->execute();
    }
}
