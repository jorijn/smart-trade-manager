<?php

namespace App\Decorator;

use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiCredentialsHttpClientDecorator extends AbstractHttpClientDecorator
{
    /**
     * {@inheritDoc}
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        // TODO make up some kind of logic for detecting when and how to sign this outgoing request

        return parent::request($method, $url, $options);
    }
}
