<?php

namespace App\Model;

use Doctrine\ORM\PersistentCollection;

class Symbol implements \JsonSerializable
{
    public const PRICE_FILTER = 'PRICE_FILTER';
    public const PERCENT_PRICE = 'PERCENT_PRICE';
    public const LOT_SIZE = 'LOT_SIZE';
    public const MIN_NOTIONAL = 'MIN_NOTIONAL';
    public const ICEBERG_PARTS = 'ICEBERG_PARTS';
    public const MARKET_LOT_SIZE = 'MARKET_LOT_SIZE';
    public const MAX_NUM_ORDERS = 'MAX_NUM_ORDERS';
    public const MAX_NUM_ALGO_ORDERS = 'MAX_NUM_ALGO_ORDERS';
    public const MAX_NUM_ICEBERG_ORDERS = 'MAX_NUM_ICEBERG_ORDERS';

    /** @var string */
    protected $symbol;
    /** @var string */
    protected $status;
    /** @var string */
    protected $baseAsset;
    /** @var int */
    protected $baseAssetPrecision;
    /** @var string */
    protected $quoteAsset;
    /** @var int */
    protected $quotePrecision;
    /** @var bool */
    protected $icebergAllowed;
    /** @var bool */
    protected $isSpotTradingAllowed;
    /** @var bool */
    protected $isMarginTradingAllowed;
    /** @var bool */
    protected $ocoAllowed;
    /** @var SymbolFilter[] */
    protected $filters = [];

    /**
     * @return string
     */
    public function getSymbol(): string
    {
        return $this->symbol;
    }

    /**
     * @param string $symbol
     *
     * @return Symbol
     */
    public function setSymbol(string $symbol): Symbol
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return Symbol
     */
    public function setStatus(string $status): Symbol
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getBaseAsset(): string
    {
        return $this->baseAsset;
    }

    /**
     * @param string $baseAsset
     *
     * @return Symbol
     */
    public function setBaseAsset(string $baseAsset): Symbol
    {
        $this->baseAsset = $baseAsset;

        return $this;
    }

    /**
     * @return int
     */
    public function getBaseAssetPrecision(): int
    {
        return $this->baseAssetPrecision;
    }

    /**
     * @param int $baseAssetPrecision
     *
     * @return Symbol
     */
    public function setBaseAssetPrecision(int $baseAssetPrecision): Symbol
    {
        $this->baseAssetPrecision = $baseAssetPrecision;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuoteAsset(): string
    {
        return $this->quoteAsset;
    }

    /**
     * @param string $quoteAsset
     *
     * @return Symbol
     */
    public function setQuoteAsset(string $quoteAsset): Symbol
    {
        $this->quoteAsset = $quoteAsset;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuotePrecision(): int
    {
        return $this->quotePrecision;
    }

    /**
     * @param int $quotePrecision
     *
     * @return Symbol
     */
    public function setQuotePrecision(int $quotePrecision): Symbol
    {
        $this->quotePrecision = $quotePrecision;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIcebergAllowed(): bool
    {
        return $this->icebergAllowed;
    }

    /**
     * @param bool $icebergAllowed
     *
     * @return Symbol
     */
    public function setIcebergAllowed(bool $icebergAllowed): Symbol
    {
        $this->icebergAllowed = $icebergAllowed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSpotTradingAllowed(): bool
    {
        return $this->isSpotTradingAllowed;
    }

    /**
     * @param bool $isSpotTradingAllowed
     *
     * @return Symbol
     */
    public function setIsSpotTradingAllowed(bool $isSpotTradingAllowed): Symbol
    {
        $this->isSpotTradingAllowed = $isSpotTradingAllowed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMarginTradingAllowed(): bool
    {
        return $this->isMarginTradingAllowed;
    }

    /**
     * @param bool $isMarginTradingAllowed
     *
     * @return Symbol
     */
    public function setIsMarginTradingAllowed(bool $isMarginTradingAllowed): Symbol
    {
        $this->isMarginTradingAllowed = $isMarginTradingAllowed;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOcoAllowed(): bool
    {
        return $this->ocoAllowed;
    }

    /**
     * @param bool $ocoAllowed
     */
    public function setOcoAllowed(bool $ocoAllowed): void
    {
        $this->ocoAllowed = $ocoAllowed;
    }

    /**
     * @return SymbolFilter[]|PersistentCollection
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param SymbolFilter[] $filters
     */
    public function setFilters(array $filters): void
    {
        foreach ($filters as $filter) {
            $filter->setSymbol($this);
        }

        $this->filters = $filters;
    }

    /**
     * @param string $filterType
     *
     * @return SymbolFilter|null
     */
    public function getFilter(string $filterType): ?SymbolFilter
    {
        foreach ($this->getFilters() as $filter) {
            if ($filter->getFilterType() === $filterType) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'symbol' => $this->symbol,
            'status' => $this->status,
            'baseAsset' => $this->baseAsset,
            'baseAssetPrecision' => $this->baseAssetPrecision,
            'quoteAsset' => $this->quoteAsset,
            'quotePrecision' => $this->quotePrecision,
            'icebergAllowed' => $this->icebergAllowed,
            'isSpotTradingAllowed' => $this->isSpotTradingAllowed,
            'isMarginTradingAllowed' => $this->isMarginTradingAllowed,
            'ocoAllowed' => $this->ocoAllowed,
        ];
    }
}
