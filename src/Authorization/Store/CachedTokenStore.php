<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Store;

use Psr\SimpleCache\CacheInterface;

class CachedTokenStore implements CacheStoreInterface
{
    private $cache;
    private $cacheKey;

    public function __construct(CacheInterface $cache, string $cacheKey)
    {
        $this->cache = $cache;
        $this->cacheKey = $cacheKey;
    }

    public function getToken(): ?string
    {
        return $this->cache->get($this->cacheKey);
    }

    public function setToken(string $token): void
    {
        $this->cache->set($this->cacheKey, $token);
    }
}
