<?php

namespace MrLinter\Metrics\Storage\PromPushGw\Tests\Client;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MrLinter\Metrics\Storage\PromPushGw\Client\HttpClient;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class HttpClientTest extends TestCase
{
    /**
     * @covers \MrLinter\Metrics\Storage\PromPushGw\Client\HttpClient::replace
     * @covers \MrLinter\Metrics\Storage\PromPushGw\Client\HttpClient::__construct
     */
    public function testReplace(): void
    {
        $jobId = 'job-123';
        $content = 'super-content';
        $uri = "http://push-gateway/metrics/job/$jobId";

        $reqFactory = $this->createMock(RequestFactoryInterface::class);
        $reqFactory
            ->expects(new InvokedCount(1))
            ->method('createRequest')
            ->with('POST', $uri)
            ->willReturn(new Request('POST', $uri));

        $http = $this->createMock(ClientInterface::class);
        $http
            ->expects(new InvokedCount(1))
            ->method('sendRequest')
            ->willReturn(new Response());

        $client = new HttpClient($http, $reqFactory,'http://push-gateway');
        $client->replace($jobId, $content);
    }
}
