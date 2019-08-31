<?php

namespace App\Bus\MessageHandler\Query;

use App\Bus\Message\Query\SymbolPriceQuery;
use App\Exception\BinanceApiException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SymbolPriceHandler
{
    /** @var HttpClientInterface */
    protected $httpClient;

    /**
     * @param HttpClientInterface $httpClient
     */
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param SymbolPriceQuery $query
     *
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     *
     * @return ResponseInterface
     */
    public function __invoke(SymbolPriceQuery $query)
    {
        $response = $this->httpClient->request('GET', 'v3/ticker/price', [
            'query' => array_filter(['symbol' => $query->getSymbol()]),
        ])->toArray(false);

        // TODO maybe create a listener for this? -> extract logic
        if (isset($response['code'])) {
            throw new BinanceApiException($response['msg'], $response['code']);
        }

        return array_reduce($response, static function (array $prices, array $symbol) {
            $prices[$symbol['symbol']] = $symbol['price'];

            return $prices;
        }, []);
    }
}
