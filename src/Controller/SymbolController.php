<?php

namespace App\Controller;

use App\Component\ExchangePriceFormatter;
use App\Exception\BinanceApiException;
use App\Model\Symbol;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SymbolController
{
    /** @var HttpClientInterface */
    protected $httpClient;
    /** @var ObjectManager */
    protected $manager;
    /** @var ExchangePriceFormatter */
    protected $formatter;

    /**
     * @param HttpClientInterface    $httpClient
     * @param ObjectManager          $manager
     * @param ExchangePriceFormatter $formatter
     */
    public function __construct(
        HttpClientInterface $httpClient,
        ObjectManager $manager,
        ExchangePriceFormatter $formatter
    ) {
        $this->httpClient = $httpClient;
        $this->manager = $manager;
        $this->formatter = $formatter;
    }

    /**
     * @param string $strSymbol
     *
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     *
     * @return JsonResponse
     */
    public function getSymbol(string $strSymbol): JsonResponse
    {
        $symbol = $this->manager->find(Symbol::class, $strSymbol);
        if (!$symbol instanceof Symbol) {
            throw new NotFoundHttpException(sprintf('symbol %s is not found', $strSymbol));
        }

        $response = $this->httpClient->request('GET', 'v3/account', [
            'extra' => ['security_type' => 'USER_DATA'],
        ])->toArray(false);

        // TODO maybe create a listener for this? -> extract logic
        if (isset($response['code'])) {
            throw new BinanceApiException($response['msg'], $response['code']);
        }

        $free = $locked = '0';
        foreach ($response['balances'] as $balance) {
            if ($balance['asset'] === $symbol->getQuoteAsset()) {
                $free = $balance['free'];
                $locked = $balance['locked'];
            }
        }

        return new JsonResponse([
            'balance_free' => $free,
            'balance_locked' => $locked,
            'account_value_in_usd' => '0',
            'symbol' => $symbol,
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function getSymbols(): JsonResponse
    {
        return new JsonResponse($this->manager->getRepository(Symbol::class)->findAll());
    }
}
