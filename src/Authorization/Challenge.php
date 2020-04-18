<?php

namespace CthulhuDen\DockerRegistryV2\Authorization;

class Challenge
{
    private $endpoint;
    private $service;
    /**
     * @psalm-var list<string>
     */
    private $scopes;

    public function __construct(string $endpoint, string $service, string ...$scopes)
    {
        $this->endpoint = $endpoint;
        $this->service = $service;
        $this->scopes = $scopes;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getService(): string
    {
        return $this->service;
    }

    /**
     * @return string[]
     * @psalm-return list<string>
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @return static
     */
    public function withScopes(string ...$scopes): self
    {
        $clone = clone $this;
        $clone->scopes = $scopes;

        return $clone;
    }
}
