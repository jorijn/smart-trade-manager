<?php

namespace App\Repository;

use App\Model\Log;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class LogRepository extends EntityRepository
{
    /**
     * @param int $id
     *
     * @return Log[]
     */
    public function getLogs(int $itemsPerPage = 10, int $page = 1): array
    {
        $qb = $this->createQueryBuilder('log');
        $qb->orderBy('log.createdAt', 'DESC');
        $qb->setMaxResults($itemsPerPage);
        $qb->setFirstResult($itemsPerPage * ($page - 1));

//        if ($id > 0) {
//            $qb->where('log.id > :id')->setParameter('id', $id);
//        }

        return $qb->getQuery()->execute();
    }

    /**
     * @throws NonUniqueResultException
     *
     * @return int
     */
    public function getAmountOfLogs(): int
    {
        $qb = $this->createQueryBuilder('log');
        $qb->select('count(log.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }
}
