<?php

namespace App\Controller;

use App\Bus\Message\Command\CreateExchangeOrdersCommand;
use App\Bus\Message\Query\ActiveTradesQuery;
use App\Bus\Message\Query\BuyOrderQuery;
use App\Form\Type\TradeType;
use App\Model\Trade;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class TradeController
{
    use HandleTrait;
    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var MessageBusInterface */
    protected $commandBus;
    /** @var ObjectManager */
    protected $manager;

    /**
     * @param MessageBusInterface  $queryBus
     * @param FormFactoryInterface $formFactory
     * @param MessageBusInterface  $commandBus
     * @param ObjectManager        $manager
     */
    public function __construct(
        MessageBusInterface $queryBus,
        FormFactoryInterface $formFactory,
        MessageBusInterface $commandBus,
        ObjectManager $manager
    ) {
        $this->messageBus = $queryBus;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->manager = $manager;
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
        $form = $this->formFactory->create(TradeType::class, new Trade());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trade = $form->getData();

            $this->manager->persist($trade);
            $this->manager->flush();

            $this->commandBus->dispatch(
                new CreateExchangeOrdersCommand(
                    ...$this->handle(new BuyOrderQuery($trade->getId()))
                )
            );

            return new JsonResponse([]);
        }

        /**
         * @var string
         * @var FormInterface $formField
         */
        $errors = [];
        foreach ($form as $fieldName => $formField) {
            $errors[$fieldName] = (string) $formField->getErrors(true);
        }

        return new JsonResponse(array_filter($errors), 422);
    }
}
