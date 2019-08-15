<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class SymbolRepository extends EntityRepository
{
    /**
     * Deletes all symbols.
     */
    public function deleteAll(): void
    {
        $this->createQueryBuilder('s')
            ->delete()
            ->getQuery()
            ->execute();
    }
}
