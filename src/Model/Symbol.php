<?php

namespace App\Model;

class Symbol
{
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
}
