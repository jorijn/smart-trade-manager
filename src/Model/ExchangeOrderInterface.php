<?php

namespace App\Model;

interface ExchangeOrderInterface
{
    /**
     * @return array
     */
    public function toApiAttributes(): array;

    /**
     * @return string
     */
    public function getEndpoint(): string;

    /**
     * @return string
     */
    public function getAttributeIdentifier(): string;

    /**
     * @return string|null
     */
    public function getAttributeIdentifierValue(): ?string;

    /**
     * @return string
     */
    public function getSymbol(): string;

    /**
     * Update the order with an Exchange specific response.
     *
     * @param array $data
     */
    public function update(array $data): void;
}
