<?php

namespace App\Model;

class SymbolFilter
{
    /** @var int */
    protected $id;
    /** @var Symbol */
    protected $symbol;
    /** @var string */
    protected $filter;
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
     * @return string
     */
    public function getFilter(): string
    {
        return $this->filter;
    }

    /**
     * @param string $filter
     *
     * @return SymbolFilter
     */
    public function setFilter(string $filter): SymbolFilter
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * This is for the serializer component
     *
     * @param string $filterType
     */
    public function setFilterType(string $filterType)
    {
        $this->filter = $filterType;

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
     * Magic setter for Serializer
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        $this->parameters[$name] = $value;
    }
}
