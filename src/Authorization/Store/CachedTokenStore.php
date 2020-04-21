<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Store;

use Psr\SimpleCache\CacheInterface;

class CachedTokenStore implements TokenStoreInterface
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
        /** @var mixed $token */
        $token = $this->cache->get($this->cacheKey);
        if (is_string($token)) {
            return $token;
        }

        return null;
    }

    public function setToken(string $token): void
    {
        $this->cache->set($this->cacheKey, $token);
    }
}
