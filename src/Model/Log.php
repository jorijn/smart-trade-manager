<?php

namespace App\Model;

use Doctrine\Common\Collections\Collection;

class Log implements \JsonSerializable
{
    /** @var int */
    protected $id;
    /** @var string */
    protected $message;
    /** @var array */
    protected $context = [];
    /** @var int */
    protected $level;
    /** @var string */
    protected $levelName;
    /** @var array */
    protected $extra = [];
    /** @var \DateTimeInterface */
    protected $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Log
     */
    public function setId(int $id): Log
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return Log
     */
    public function setMessage(string $message): Log
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array $context
     *
     * @return Log
     */
    public function setContext(array $context): Log
    {
        $this->context = json_decode(json_encode($context), true) ?? [];

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return Log
     */
    public function setLevel(int $level): Log
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return string
     */
    public function getLevelName(): string
    {
        return $this->levelName;
    }

    /**
     * @param string $levelName
     *
     * @return Log
     */
    public function setLevelName(string $levelName): Log
    {
        $this->levelName = $levelName;

        return $this;
    }

    /**
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra;
    }

    /**
     * @param array $extra
     *
     * @return Log
     */
    public function setExtra(array $extra): Log
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeInterface $createdAt
     *
     * @return Log
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): Log
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return array_map(static function ($value) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format('r');
            }

            return $value;
        }, get_object_vars($this));
    }
}
