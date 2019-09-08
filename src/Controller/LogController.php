<?php

namespace App\Controller;

use App\Model\Log;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class LogController
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
     * @param Request $request
     *
     * @throws NonUniqueResultException
     *
     * @return JsonResponse
     */
    public function getLogs(Request $request): JsonResponse
    {
        $itemsPerPage = $request->get('itemsPerPage', 10);
        $page = $request->get('page', 1);

        $repository = $this->manager->getRepository(Log::class);

        return new JsonResponse([
            'items' => $repository->getLogs($itemsPerPage, $page),
            'total' => $repository->getAmountOfLogs(),
        ]);
    }
}
