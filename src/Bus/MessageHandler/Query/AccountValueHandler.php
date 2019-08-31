<?php

namespace App\Bus\MessageHandler\Query;

use App\Bus\Message\Query\AccountValueQuery;
use App\Bus\Message\Query\BalanceQuery;
use App\Bus\Message\Query\SymbolPriceQuery;
use App\Model\Symbol;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AccountValueHandler implements LoggerAwareInterface
{
    protected const DEFAULT_ASSET = 'BTC';

    use LoggerAwareTrait;
    use HandleTrait;

    /** @var ObjectManager */
    protected $manager;
    /** @var HttpClientInterface */
    protected $httpClient;

    /**
     * @param ObjectManager       $manager
     * @param HttpClientInterface $httpClient
     * @param MessageBusInterface $queryBus
     * @param LoggerInterface     $logger
     */
    public function __construct(
        ObjectManager $manager,
        HttpClientInterface $httpClient,
        MessageBusInterface $queryBus,
        LoggerInterface $logger
    ) {
        $this->manager = $manager;
        $this->httpClient = $httpClient;
        $this->messageBus = $queryBus;

        $this->setLogger($logger);
    }

    /**
     * @param AccountValueQuery $query
     *
     * @return array
     */
    public function __invoke(AccountValueQuery $query)
    {
        $accountValue = [];
        $uniqueQuoteAssets = $this->manager->getRepository(Symbol::class)->getUniqueQuoteAssets();
        $balances = array_filter($this->handle(new BalanceQuery()), static function (array $item) {
            return bccomp(bcadd($item['free'], $item['locked'], 8), '0', 8) === 1;
        });
        $prices = $this->handle(new SymbolPriceQuery());

        // first, convert everything to the default asset
        $accountValue[self::DEFAULT_ASSET] = array_reduce(
            array_keys($balances),
            function (string $value, string $asset) use ($balances, $prices) {
                $quantity = bcadd($balances[$asset]['free'], $balances[$asset]['locked'], 8);
                $worth = $this->getPrice($prices, $quantity, self::DEFAULT_ASSET, $asset);

                return bcadd($value, $worth, 8);
            },
            '0'
        );

        // then, enrich the other assets with the default asset
        foreach (array_diff($uniqueQuoteAssets, array_keys($accountValue)) as $quoteAssetToCalculate) {
            $accountValue[$quoteAssetToCalculate] = $this->getPrice(
                $prices,
                $accountValue[self::DEFAULT_ASSET],
                $quoteAssetToCalculate,
                self::DEFAULT_ASSET
            );
        }

        return $accountValue;
    }

    /**
     * @param array  $prices
     * @param string $quantity
     * @param string $asset
     * @param string $quoteAsset
     *
     * @return string
     */
    protected function getPrice(array $prices, string $quantity, string $asset, string $quoteAsset): string
    {
        if (array_key_exists($quoteAsset.$asset, $prices)) {
            return bcmul($quantity, $prices[$quoteAsset.$asset], 8);
        }

        if (array_key_exists($asset.$quoteAsset, $prices)) {
            return bcdiv($quantity, $prices[$asset.$quoteAsset], 8);
        }

        return '0';
    }
}
