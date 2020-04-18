<?php

namespace CthulhuDen\DockerRegistryV2;

use CthulhuDen\DockerRegistryV2\Model\BlobInfo;
use CthulhuDen\DockerRegistryV2\Model\ImageId;
use CthulhuDen\DockerRegistryV2\Model\ImageRepository;
use CthulhuDen\DockerRegistryV2\Model\Manifest;
use Nyholm\Psr7\Stream;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriFactoryInterface;

class Client
{
    private const MANIFEST_TYPE = 'application/vnd.docker.distribution.manifest.v2+json';

    private $http;
    private $requestFactory;
    private $uriFactory;
    private $endpoint;

    public function __construct(
        ClientInterface $http,
        RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory,
        string $endpoint
    )
    {
        $this->http = $http;
        $this->requestFactory = $requestFactory;
        $this->uriFactory = $uriFactory;
        $this->endpoint = rtrim($endpoint, '/') . '/v2/';
    }

    public function checkApi(): ResponseInterface
    {
        return $this->sendAndExpect2xx($this->buildRequest('GET', ''));
    }

    public function getManifest(ImageId $image): Manifest
    {
        $request = $this->buildRequest('GET', "{$image->getName()}/manifests/{$image->getTag()}")
            ->withHeader('Accept', self::MANIFEST_TYPE);

        $response = $this->sendAndExpect2xx($request);

        return new Manifest($response->getBody()->getContents());
    }

    public function putManifest(ImageId $image, Manifest $manifest): void
    {
        $request = $this->buildRequest('PUT', "{$image->getName()}/manifests/{$image->getTag()}")
            ->withHeader('Content-type', self::MANIFEST_TYPE)
            ->withBody(Stream::create((string)$manifest));

        $this->sendAndExpect2xx($request);
    }

    public function checkBlob(ImageRepository $repository, string $blobDigest): ResponseInterface
    {
        $request = $this->buildRequest('HEAD', "{$repository->getName()}/blobs/{$blobDigest}");

        return $this->sendAndExpect2xx($request);
    }

    public function mountBlob(ImageRepository $target, ImageRepository $source, string $blobDigest): ResponseInterface
    {
        $request = $this->buildRequest(
            'POST',
            "{$target->getName()}/blobs/uploads/?" . http_build_query([
                'mount' => $blobDigest,
                'from' => $source->getName(),
            ]),
        );

        return $this->sendAndExpect2xx($request);
    }

    public function downloadBlob(ImageRepository $repository, BlobInfo $blob): ResponseInterface
    {
        $request = $this->buildRequest('GET', "{$repository->getName()}/blobs/{$blob->getDigest()}")
            ->withHeader('Accept', $blob->getType());

        return $this->sendAndExpect2xx($request);
    }

    public function retag(ImageId $existing, ImageId $new): void
    {
        $manifest = $this->getManifest($existing);

        if ($existing->getName() !== $new->getName()) {
            $this->mountBlob(
                $new->getRepository(),
                $existing->getRepository(),
                $manifest->getConfig()->getDigest(),
            );

            foreach ($manifest->getLayers() as $layer) {
                $this->mountBlob(
                    $new->getRepository(),
                    $existing->getRepository(),
                    $layer->getDigest(),
                );
            }
        }

        $this->putManifest($new, $manifest);
    }

    private function buildRequest(string $method, string $path): RequestInterface
    {
        $path = ltrim($path, '/');

        return $this->requestFactory->createRequest($method, "{$this->endpoint}{$path}");
    }

    private function sendAndExpect2xx(RequestInterface $request): ResponseInterface
    {
        $response = $this->http->sendRequest($request);

        if ($response->getStatusCode() <= 299) {
            return $response;
        }

        if ($response->getStatusCode() === 307) {
            return $this->sendAndExpect2xx(
                $request->withUri($this->uriFactory->createUri($response->getHeaderLine('Location')))
            );
        }

        throw new InvalidResponseException("Non-2xx response code: {$response->getStatusCode()}", $response);
    }
}
