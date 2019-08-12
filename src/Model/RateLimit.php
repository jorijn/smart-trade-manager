<?php

namespace App\Model;

class RateLimit
{
    /** @var string */
    protected $rateLimitType;
    /** @var string */
    protected $interval;
    /** @var int */
    protected $intervalNum;
    /** @var int */
    protected $limit;

    /**
     * @return string
     */
    public function getRateLimitType(): string
    {
        return $this->rateLimitType;
    }

    /**
     * @param string $rateLimitType
     *
     * @return RateLimit
     */
    public function setRateLimitType(string $rateLimitType): RateLimit
    {
        $this->rateLimitType = $rateLimitType;

        return $this;
    }

    /**
     * @return string
     */
    public function getInterval(): string
    {
        return $this->interval;
    }

    /**
     * @param string $interval
     *
     * @return RateLimit
     */
    public function setInterval(string $interval): RateLimit
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * @return int
     */
    public function getIntervalNum(): int
    {
        return $this->intervalNum;
    }

    /**
     * @param int $intervalNum
     *
     * @return RateLimit
     */
    public function setIntervalNum(int $intervalNum): RateLimit
    {
        $this->intervalNum = $intervalNum;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return RateLimit
     */
    public function setLimit(int $limit): RateLimit
    {
        $this->limit = $limit;

        return $this;
    }
}
