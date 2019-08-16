<?php

namespace App\Decorator;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiCredentialsHttpClientDecorator extends AbstractHttpClientDecorator
{
    const HASH_ALGO = 'sha256';

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
     * This decorator implementation on the HttpClientInterface will check the given options for
     * a specific type of security key, depending on which — the decorator will sign the request
     * and add the API key to the header array.
     *
     * {@inheritdoc}
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $extra = $options['extra'] ?? [];

        switch ($extra['security_type'] ?? null) {
            case 'TRADE':
            case 'USER_DATA':
                [$method, $url, $options] = $this->addSignatureToRequest($method, $url, $options);
                // no break
            case 'USER_STREAM':
            case 'MARKET_DATA':
                /* @noinspection SuspiciousAssignmentsInspection */
                [$method, $url, $options] = $this->addApiKeyToRequest($method, $url, $options);
                // no break
            case 'NONE':
            default:
                return parent::request($method, $url, $options);
        }
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $options
     *
     * @return array
     */
    protected function addSignatureToRequest(string $method, string $url, array $options): array
    {
        // fetch the query and add the required timestamp
        $parameters = $options['query'] ?? [];

        // check and validate any present body
        if (isset($options['body'])) {
            if (!is_array($options['body'])) {
                throw new \InvalidArgumentException(
                    'passing any other request body than type `array` on '.__CLASS__.' is not supported'
                );
            }

            $parameters = array_merge($parameters, $options['body']);
        }

        // clear up the request as we are overwriting
        unset($options['query'], $options['body']);

        // add the timestamp so the exchange can invalidate when there is too much network lag
        $parameters['timestamp'] = number_format(microtime(true) * 1000, 0, '.', '');

        // build the query string and hash it into a signature
        $parameterString = http_build_query($parameters, '', '&');
        $signature = hash_hmac(self::HASH_ALGO, $parameterString, $this->apiSecret);

        // if the request was designed to be a POST request, take all the options and move them to the body --
        // only signature is allowed as query string
        if ($method === 'POST') {
            $options['body'] = array_merge($parameters, ['signature' => $signature]);
        } else {
            $options['query'] = array_merge($parameters, ['signature' => $signature]);
        }

        return [$method, $url, $options];
    }

    /**
     * @param string $method
     * @param string $url
     * @param array  $options
     *
     * @return array
     */
    protected function addApiKeyToRequest(string $method, string $url, array $options): array
    {
        $options['headers'] = array_merge($options['headers'] ?? [], ['X-MBX-APIKEY' => $this->apiKey]);

        return [$method, $url, $options];
    }
}
