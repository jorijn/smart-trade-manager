<?php

namespace App\Bus\MessageHandler\Query;

use App\Bus\Message\Query\BalanceQuery;
use App\Exception\BinanceApiException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BalanceHandler
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
     * @param BalanceQuery $query
     *
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     *
     * @return array
     */
    public function __invoke(BalanceQuery $query)
    {
        $response = $this->httpClient->request('GET', 'v3/account', [
            'extra' => ['security_type' => 'USER_DATA'],
        ])->toArray(false);

        // TODO maybe create a listener for this? -> extract logic
        if (isset($response['code'])) {
            throw new BinanceApiException($response['msg'], $response['code']);
        }

        return array_reduce($response['balances'], static function (array $balances, array $asset) {
            $balances[$asset['asset']] = ['free' => $asset['free'], 'locked' => $asset['locked']];

            return $balances;
        }, []);
    }
}
