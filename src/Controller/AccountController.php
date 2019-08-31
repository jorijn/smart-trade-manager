<?php

namespace App\Controller;

use App\Bus\Message\Query\AccountValueQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class AccountController
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
    public function getAccountValue(): JsonResponse
    {
        return new JsonResponse($this->handle(new AccountValueQuery()));
    }
}
