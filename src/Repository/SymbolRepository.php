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

    /**
     * @return mixed
     */
    public function getUniqueQuoteAssets()
    {
        $results = $this
            ->createQueryBuilder('s')
            ->select('s.quoteAsset')
            ->distinct()
            ->getQuery()
            ->execute();

        return array_map(static function (array $record) {
            return $record['quoteAsset'];
        }, $results);
    }
}
