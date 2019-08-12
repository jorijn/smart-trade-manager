<?php

namespace App\Repository;

use App\Model\Symbol;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;

class SymbolRepository extends EntityRepository
{
    /**
     * gets all symbols in the following format: [BTCUSDT => ..., BTCETC => ..., ETHXRP => ...].
     *
     * @return Symbol[]
     */
    public function associativeFindAll(): array
    {
        return array_reduce($this->findAll(), static function (array $carry, Symbol $item) {
            $carry[$item->getSymbol()] = $carry;

            return $carry;
        }, []);
    }

    /**
     * @param string[] $symbols
     */
    public function removeSymbols(string ...$symbols): void
    {
        $queryBuilder = $this
            ->createQueryBuilder('s')
            ->where('s.symbol IN (:symbols)')
            ->setParameter('symbols', $symbols, Connection::PARAM_STR_ARRAY)
            ->delete();

        $queryBuilder->getQuery()->execute();
    }
}
