<?php

namespace App\Repository;

use App\Model\ExchangeOcoOrder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

class ExchangeOcoOrderRepository extends EntityRepository
{
    /**
     * @return ExchangeOcoOrder[]|ArrayCollection
     */
    public function getPendingOrders()
    {
        $qb = $this->createQueryBuilder('eoo');
        $qb
            ->where('eoo.listOrderStatus != :allDoneStatus')
            ->orderBy('eoo.orderListId', 'ASC');

        $qb->setParameter('allDoneStatus', 'ALL_DONE');

        return $qb->getQuery()->execute();
    }
}
