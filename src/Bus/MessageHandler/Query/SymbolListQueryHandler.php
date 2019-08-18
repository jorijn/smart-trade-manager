<?php

namespace App\Bus\MessageHandler\Query;

use App\Bus\Message\Query\SymbolListQuery;
use App\Model\ExchangeInfo;
use App\Model\Symbol;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SymbolListQueryHandler implements MessageHandlerInterface
{
    /** @var HttpClientInterface */
    protected $binanceApiClient;
    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @param HttpClientInterface $binanceApiClient
     * @param SerializerInterface $serializer
     */
    public function __construct(HttpClientInterface $binanceApiClient, SerializerInterface $serializer)
    {
        $this->binanceApiClient = $binanceApiClient;
        $this->serializer = $serializer;
    }

    /**
     * @param SymbolListQuery $command
     *
     *@throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     *
     * @return Symbol[]|array
     */
    public function __invoke(SymbolListQuery $command)
    {
        $response = $this->binanceApiClient->request('GET', 'v1/exchangeInfo');

        /** @var ExchangeInfo $exchangeInfo */
        $exchangeInfo = $this->serializer->deserialize($response->getContent(), ExchangeInfo::class, 'json');

        return $exchangeInfo->getSymbols();
    }
}
