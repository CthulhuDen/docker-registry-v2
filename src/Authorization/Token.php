<?php

namespace CthulhuDen\DockerRegistryV2\Authorization;

class Token
{
    private $token;
    private $scopes;

    /**
     * @param string[] $scopes
     * @psalm-param list<string> $scopes
     */
    public function __construct(string $token, array $scopes)
    {
        $this->token = $token;
        $this->scopes = $scopes;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @psalm-return list<string>
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }
}
