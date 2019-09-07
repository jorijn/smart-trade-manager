<?php

namespace App\Controller;

use App\Bus\Message\Query\BalanceQuery;
use App\Bus\Message\Query\SymbolPriceQuery;
use App\Component\ExchangePriceFormatter;
use App\Model\Symbol;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SymbolController
{
    use HandleTrait;

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
     * @param MessageBusInterface    $queryBus
     */
    public function __construct(
        HttpClientInterface $httpClient,
        ObjectManager $manager,
        ExchangePriceFormatter $formatter,
        MessageBusInterface $queryBus
    ) {
        $this->httpClient = $httpClient;
        $this->manager = $manager;
        $this->formatter = $formatter;
        $this->messageBus = $queryBus;
    }

    /**
     * @param string $strSymbol
     *
     * @return JsonResponse
     */
    public function getSymbol(string $strSymbol): JsonResponse
    {
        $symbol = $this->manager->find(Symbol::class, $strSymbol);
        if (!$symbol instanceof Symbol) {
            throw new NotFoundHttpException(sprintf('symbol %s is not found', $strSymbol));
        }

        $balances = $this->handle(new BalanceQuery());
        $prices = $this->handle(new SymbolPriceQuery());
        $quoteAsset = $symbol->getQuoteAsset();

        if (array_key_exists($quoteAsset, $balances)) {
            $free = $balances[$quoteAsset]['free'];
            $locked = $balances[$quoteAsset]['locked'];
        }

        if (array_key_exists($symbol->getSymbol(), $prices)) {
            $price = $this->formatter->roundTicks($symbol, $prices[$symbol->getSymbol()]);
        }

        return new JsonResponse([
            'balance_free' => $free ?? 0,
            'balance_locked' => $locked ?? 0,
            'symbol' => $symbol,
            'price' => $price ?? null,
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
