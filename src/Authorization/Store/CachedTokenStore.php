<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Store;

use CthulhuDen\DockerRegistryV2\Authorization\Token;
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

    public function getToken(): ?Token
    {
        /** @var mixed $token */
        $token = $this->cache->get($this->cacheKey);
        if (is_string($token)) {
            /** @var mixed $token */
            $token = unserialize($token);
            if ($token instanceof Token) {
                return $token;
            }
        }

        return null;
    }

    public function setToken(Token $token): void
    {
        $this->cache->set($this->cacheKey, serialize($token));
    }
}
