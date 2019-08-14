<?php

namespace App\Decorator;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiCredentialsHttpClientDecorator extends AbstractHttpClientDecorator
{
    /** @var string */
    protected $apiKey;
    /** @var string */
    protected $apiSecret;

    /**
     * @param HttpClientInterface $httpClient
     * @param string              $apiKey
     * @param string              $apiSecret
     */
    public function __construct(HttpClientInterface $httpClient, string $apiKey, string $apiSecret)
    {
        parent::__construct($httpClient);

        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * {@inheritDoc}
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        // TODO make up some kind of logic for detecting when and how to sign this outgoing request

        return parent::request($method, $url, $options);
    }
}
