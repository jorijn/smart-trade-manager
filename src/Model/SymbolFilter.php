<?php

namespace App\Model;

class SymbolFilter
{
    /** @var int */
    protected $id;
    /** @var Symbol */
    protected $symbol;
    /** @var string */
    protected $filterType;
    /** @var array */
    protected $parameters = [];

    /**
     * @return Symbol
     */
    public function getSymbol(): Symbol
    {
        return $this->symbol;
    }

    /**
     * @param Symbol $symbol
     *
     * @return SymbolFilter
     */
    public function setSymbol(Symbol $symbol): SymbolFilter
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFilterType(): string
    {
        return $this->filterType;
    }

    /**
     * @param string $filterType
     *
     * @return SymbolFilter
     */
    public function setFilterType(string $filterType): SymbolFilter
    {
        $this->filterType = $filterType;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Magic setter for Serializer.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set(string $name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param string $parameter
     *
     * @return mixed|null
     */
    public function getParameter(string $parameter): ?string
    {
        return $this->parameters[$parameter] ?? null;
    }
}
