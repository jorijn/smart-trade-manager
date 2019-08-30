<?php

namespace App\Bus\MessageHandler\Query;

use App\Bus\Message\Query\ActiveTradesQuery;
use App\Model\Trade;
use Doctrine\Common\Persistence\ObjectManager;

class ActiveTradesHandler
{
    /** @var ObjectManager */
    protected $manager;

    /**
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param ActiveTradesQuery $query
     *
     * @return Trade[]
     */
    public function __invoke(ActiveTradesQuery $query)
    {
        return $this->manager->getRepository(Trade::class)->getActiveTrades();
    }
}
