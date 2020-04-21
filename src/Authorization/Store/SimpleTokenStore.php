<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Store;

final class SimpleTokenStore implements TokenStoreInterface
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
