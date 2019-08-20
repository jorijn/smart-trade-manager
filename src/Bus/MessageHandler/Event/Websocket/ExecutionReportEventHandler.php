<?php

namespace App\Bus\MessageHandler\Event\Websocket;

use App\Bus\Message\Event\WebsocketEvent;
use App\Event\OrderUpdatedEvent;
use App\Model\ExchangeOrder;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExecutionReportEventHandler implements WebsocketEventHandlerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ObjectManager */
    protected $manager;
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param ObjectManager            $manager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ObjectManager $manager, EventDispatcherInterface $dispatcher)
    {
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param WebsocketEvent $event
     */
    public function handle(WebsocketEvent $event): void
    {
        $payload = $event->getPayload();
        $order = $this->manager->getRepository(ExchangeOrder::class)->findOneByOrderId($payload['i']);

        if (!$order instanceof ExchangeOrder) {
            $this->logger->notice('received execution report for unknown order', [
                'payload' => $payload,
            ]);

            return;
        }

        if ($order->getUpdatedAt() > $payload['E']) {
            $this->logger->info('event update time was older than or equal to most recent timestamp, ignoring', [
                'order' => $order,
                'payload' => $payload,
            ]);

            return;
        }

        $order->setStatus($payload['X']);
        $order->setFilledQuantity($payload['z']);
        $order->setFilledQuoteQuantity($payload['Z']);

        $this->manager->persist($order);
        $this->manager->flush();

        $this->logger->info('processed execution report for order', [
            'order' => $order,
            'payload' => $payload,
        ]);

        $this->dispatcher->dispatch(new OrderUpdatedEvent($order));
    }

    /**
     * @param WebsocketEvent $event
     *
     * @return bool
     */
    public function supports(WebsocketEvent $event): bool
    {
        return $event->getType() === 'executionReport';
    }
}
