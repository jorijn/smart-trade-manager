<?php

namespace App\Bus\MessageHandler\Command;

use App\Bus\Message\Command\CreateExchangeOrdersCommand;
use App\Event\OrderCreatedEvent;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CreateExchangeOrdersHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var HttpClientInterface */
    protected $binanceApiClient;
    /** @var ObjectManager */
    protected $manager;
    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param HttpClientInterface      $binanceApiClient
     * @param LoggerInterface          $logger
     * @param ObjectManager            $manager
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(HttpClientInterface $binanceApiClient, LoggerInterface $logger, ObjectManager $manager, EventDispatcherInterface $dispatcher)
    {
        $this->binanceApiClient = $binanceApiClient;
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
        $this->setLogger($logger);
    }

    /**
     * @param CreateExchangeOrdersCommand $command
     *
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     */
    public function __invoke(CreateExchangeOrdersCommand $command)
    {
        $this->logger->info('starting dispatching of new order batch');

        foreach ($command->getOrders() as $order) {
            $this->logger->info('dispatching order', ['order' => $order]);

            $result = $this->binanceApiClient->request('POST', 'v3/order', [
                'extra' => ['security_type' => 'TRADE'],
                'body' => $order->toApiAttributes(),
            ]);

            $result = $result->toArray(false);

            // TODO maybe create a listener for this? -> extract logic
            if (isset($result['code'])) {
                $this->logger->error('failed to create order', [
                    'order' => $order,
                    'code' => $result['code'],
                    'reason' => $result['msg'],
                ]);

                continue;
            }

            $order->setOrderId($result['orderId']);
            $order->setUpdatedAt($result['transactTime']);
            $order->setStatus($result['status']);
            $order->setQuantity($result['origQty']);
            $order->setFilledQuantity($result['executedQty'] ?? null);
            $order->setFilledQuoteQuantity($result['cummulativeQuoteQty'] ?? null);
            $order->setTimeInForce($result['timeInForce']);
            $order->setType($result['type']);
            $order->setSide($result['side']);

            $this->manager->persist($order);

            $this->dispatcher->dispatch(new OrderCreatedEvent($order));
        }

        $this->manager->flush();
        $this->logger->notice('end dispatching of new order batch');
    }
}
