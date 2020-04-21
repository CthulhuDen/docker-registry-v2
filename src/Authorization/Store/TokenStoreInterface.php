<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Store;

interface TokenStoreInterface
{
    public function getToken(): ?string;

    public function setToken(string $token): void;
}
