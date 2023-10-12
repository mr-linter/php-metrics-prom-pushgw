<?php

namespace MrLinter\Metrics\Storage\PromPushGw\Client;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as Http;
use Psr\Http\Message\RequestFactoryInterface;

final class HttpClient implements Client
{
    public function __construct(
        private readonly Http $http,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly string $address,
    ) {
    }

    public function replace(string $job, string $data): void
    {
        $url = sprintf("%s/metrics/job/%s", $this->address, $job);

        try {
            $this
                ->http
                ->sendRequest($this
                    ->requestFactory
                    ->createRequest('POST', $url)
                    ->withBody(Utils::streamFor($data)),
                );
        } catch (ClientExceptionInterface $e) {
            throw new ReplaceException(sprintf('Replace metrics was failed: %s', $e->getMessage()), previous: $e);
        }
    }
}

