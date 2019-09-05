<?php

namespace App\Decorator;

use App\Exception\BinanceApiException;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ErrorHandlerHttpClientDecorator extends AbstractHttpClientDecorator
{
    /**
     * {@inheritdoc}
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $response = parent::request($method, $url, $options);

        $data = $response->toArray(false);
        if (isset($data['code'])) {
            throw new BinanceApiException($data['msg'], $data['code']);
        }

        return $response;
    }
}
