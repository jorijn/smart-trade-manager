<?php

namespace App\Model;

class ExchangeInfo
{
    /** @var int */
    protected $serverTime;
    /** @var string */
    protected $timezone;
    /** @var RateLimit[] */
    protected $rateLimits;
    /** @var string[] */
    protected $exchangeFilters;
    /** @var Symbol[] */
    protected $symbols;

    /**
     * @return int
     */
    public function getServerTime(): int
    {
        return $this->serverTime;
    }

    /**
     * @param int $serverTime
     *
     * @return ExchangeInfo
     */
    public function setServerTime(int $serverTime): ExchangeInfo
    {
        $this->serverTime = $serverTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     *
     * @return ExchangeInfo
     */
    public function setTimezone(string $timezone): ExchangeInfo
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return RateLimit[]
     */
    public function getRateLimits(): array
    {
        return $this->rateLimits;
    }

    /**
     * @param RateLimit[] $rateLimits
     *
     * @return ExchangeInfo
     */
    public function setRateLimits(array $rateLimits): ExchangeInfo
    {
        $this->rateLimits = $rateLimits;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getExchangeFilters(): array
    {
        return $this->exchangeFilters;
    }

    /**
     * @param string[] $exchangeFilters
     *
     * @return ExchangeInfo
     */
    public function setExchangeFilters(array $exchangeFilters): ExchangeInfo
    {
        $this->exchangeFilters = $exchangeFilters;

        return $this;
    }

    /**
     * @return Symbol[]
     */
    public function getSymbols(): array
    {
        return $this->symbols;
    }

    /**
     * @param Symbol[] $symbols
     *
     * @return ExchangeInfo
     */
    public function setSymbols(array $symbols): ExchangeInfo
    {
        $this->symbols = $symbols;

        return $this;
    }
}
