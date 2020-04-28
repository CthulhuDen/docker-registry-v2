<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Store;

use CthulhuDen\DockerRegistryV2\Authorization\Token;

final class SimpleTokenStore implements TokenStoreInterface
{
    /** @var Token|null */
    private $token = null;

    public function getToken(): ?Token
    {
        return $this->token;
    }

    public function setToken(Token $token): void
    {
        $this->token = $token;
    }
}
