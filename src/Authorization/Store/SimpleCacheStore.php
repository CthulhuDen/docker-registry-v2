<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Store;

final class SimpleCacheStore implements CacheStoreInterface
{
    private $token;

    public function __construct(string $token = null)
    {
        $this->token = $token;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}
