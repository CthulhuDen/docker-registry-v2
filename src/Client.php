<?php

namespace CthulhuDen\DockerRegistryV2;

use Nyholm\Psr7\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client
{
    private const MANIFEST_TYPE = 'application/vnd.docker.distribution.manifest.v2+json';

    private $http;
    private $requestFactory;
    private $endpoint;

    public function __construct(
        ClientInterface $http,
        RequestFactoryInterface $requestFactory,
        string $endpoint
    ) {
        $this->http = $http;
        $this->requestFactory = $requestFactory;
        $this->endpoint = rtrim($endpoint, '/') . '/v2';
    }

    public function getManifest(string $image, string $tag = 'latest'): string
    {
        $request = $this->buildRequest('GET', "/{$image}/manifests/{$tag}")
            ->withHeader('Accept', self::MANIFEST_TYPE);

        $response = $this->sendAndExpect2xx($request);

        return $response->getBody()->getContents();
    }

    public function putManifest(string $image, string $tag, string $manifest): void
    {
        $request = $this->buildRequest('PUT', "/{$image}/manifests/{$tag}")
            ->withHeader('Content-type', self::MANIFEST_TYPE)
            ->withBody(Stream::create($manifest));

        $this->sendAndExpect2xx($request);
    }

    private function buildRequest(string $method, string $path): RequestInterface
    {
        return $this->requestFactory->createRequest($method, "{$this->endpoint}{$path}");
    }

    private function sendAndExpect2xx(RequestInterface $request): ResponseInterface
    {
        $response = $this->http->sendRequest($request);

        if ($response->getStatusCode() > 299) {
            throw new InvalidResponseException("Non-2xx response code: {$response->getStatusCode()}", $response);
        }

        return $response;
    }
}
