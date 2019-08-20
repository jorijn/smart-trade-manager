<?php

namespace App\Repository;

use App\Model\Order;
use Doctrine\ORM\EntityRepository;

class OrderRepository extends EntityRepository
{
    /**
     * @param string $clientOrderId
     *
     * @return Order|null
     */
    public function findOneByClientOrderId(string $clientOrderId): ?Order
    {
        return $this->findOneBy(['clientOrderId' => $clientOrderId]);
    }
}
