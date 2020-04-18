# Docker Registry API v2 PHP Client

## Installation

```
composer require cthulhu/docker-registry-v2
```

## Example

In the example below we define or own logging http client wrapper and simple file cache implementation,
but in practice you probably have http client and cache driver already available.

```php
<?php

use Buzz\Client\Curl as CurlClient;
use CthulhuDen\DockerRegistryV2\Authorization\AuthorizingClient;
use CthulhuDen\DockerRegistryV2\Authorization\Challenge\ChallengeParser;
use CthulhuDen\DockerRegistryV2\Authorization\Store\CachedTokenStore;
use CthulhuDen\DockerRegistryV2\Client as RegistryClient;
use CthulhuDen\DockerRegistryV2\ImageRepository;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

require __DIR__ . '/vendor/autoload.php';

class LoggingClient implements ClientInterface
{
    private $inner;

    public function __construct(ClientInterface $inner)
    {
        $this->inner = $inner;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        echo "{$request->getMethod()} {$request->getUri()} >> ";
        $start = microtime(true);

        $response = $this->inner->sendRequest($request);

        $spent = microtime(true) - $start;
        printf("%d (spent %.3fs)\n", $response->getStatusCode(), $spent);

        return $response;
    }
}

class SimpleFileCache implements CacheInterface
{
    private $filepath;

    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
    }

    public function get($key, $default = null)
    {
        if ((@$value = file_get_contents($this->filepath)) !== false) {
            return $value;
        }

        return $default;
    }

    public function set($key, $value, $ttl = null)
    {
        file_put_contents($this->filepath, (string) $value);
    }

    public function delete($key)
    {
        @unlink($this->filepath);
    }

    public function clear()
    {
        @unlink($this->filepath);
    }

    public function getMultiple($keys, $default = null)
    {
        $value = @file_get_contents($this->filepath);
        if ($value === false) {
            $value = $default;
        }

        $return = [];
        foreach ($keys as $key) {
            $return[$key] = $value;
        }

        return $return;
    }

    public function setMultiple($values, $ttl = null)
    {
        $value = null;
        $set = false;
        foreach ($values as $value) {
            $set = true;
        }

        if (!$set) {
            return;
        }

        file_put_contents($this->filepath, (string) $value);
    }

    public function deleteMultiple($keys)
    {
        @unlink($this->filepath);
    }

    public function has($key)
    {
        return file_exists($this->filepath);
    }
}

$psr17Factory = new Psr17Factory();

// Create HTTP client - could use any Psr-18 implementation
$client = new LoggingClient(new CurlClient($psr17Factory));

// Configure credentials and tokens store. Can be skipped if registry does not require authorization.
$client = new AuthorizingClient(
    $client,
    new ChallengeParser(),
    $psr17Factory,
    // Or just use SimpleTokenStore if you do not need token caching
    new CachedTokenStore(new SimpleFileCache(__DIR__ . '/.token'), 'token'),
    'username',
    'password',
);

// Finally create the API client.
$registryClient = new RegistryClient($client, $psr17Factory, 'https://registry.example.com');

// We are going to push new tag (based on existing) in this image of the given registry.
$imageRepo = new ImageRepository('group/repo/image');

$manifest = $registryClient->getManifest($imageRepo->withTag('latest'));
$registryClient->putManifest($imageRepo->withTag('retagged-latest'), $manifest);
```
