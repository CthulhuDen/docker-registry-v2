<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Store;

use CthulhuDen\DockerRegistryV2\Authorization\Token;

interface TokenStoreInterface
{
    public function getToken(): ?Token;

    public function setToken(Token $token): void;
}
