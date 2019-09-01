<?php

namespace App\Controller;

use App\Bus\Message\Query\ActiveTradesQuery;
use App\Form\Type\TradeType;
use App\Model\Trade;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class TradeController
{
    use HandleTrait;
    /** @var FormFactoryInterface */
    protected $formFactory;

    /**
     * @param MessageBusInterface  $queryBus
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(MessageBusInterface $queryBus, FormFactoryInterface $formFactory)
    {
        $this->messageBus = $queryBus;
        $this->formFactory = $formFactory;
    }

    /**
     * @return JsonResponse
     */
    public function getActiveTrades(): JsonResponse
    {
        return new JsonResponse($this->handle(new ActiveTradesQuery()));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postNewTrade(Request $request): JsonResponse
    {
        // TODO move this to a request listener
        // data comes in as JSON, form can't handle that

        $form = $this->formFactory->create(TradeType::class, new Trade());
        $form->handleRequest($request);

        dump($request->getContent());
        if ($form->isSubmitted() && $form->isValid()) {
            $trade = $form->getData();
            // TODO finish
            return new JsonResponse([]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getCause()->getPropertyPath()] = $error->getMessage();
        }

        return new JsonResponse($errors, 422);
    }
}
