<?php

namespace App\Decorator;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class VerboseHttpClientDecorator extends AbstractHttpClientDecorator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var bool */
    protected $enabled;

    /**
     * @param HttpClientInterface $httpClient
     * @param bool                $enabled
     */
    public function __construct(HttpClientInterface $httpClient, bool $enabled = false)
    {
        parent::__construct($httpClient);
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if ($this->enabled) {
            $options['on_progress'] = function (int $dlNow, int $dlSize, array $info) use ($url, $method): void {
                $this->logger->info(sprintf('%s %s progress update', $method, $url), [
                    'dl_now' => $dlNow,
                    'dl_size' => $dlSize,
                    'info' => $info,
                ]);
            };
        }

        return parent::request($method, $url, $options);
    }
}
