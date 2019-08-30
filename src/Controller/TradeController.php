<?php

namespace App\Controller;

use App\Bus\Message\Query\ActiveTradesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class TradeController extends AbstractController
{
    use HandleTrait;

    /**
     * @param MessageBusInterface $queryBus
     */
    public function __construct(MessageBusInterface $queryBus)
    {
        $this->messageBus = $queryBus;
    }

    /**
     * @return JsonResponse
     */
    public function getActiveTrades(): JsonResponse
    {
        return $this->json($this->handle(new ActiveTradesQuery()));
    }
}
