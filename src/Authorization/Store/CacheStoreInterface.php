<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Store;

interface CacheStoreInterface
{
    public function getToken(): ?string;

    public function setToken(string $token): void;
}
