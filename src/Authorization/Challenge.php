<?php

namespace CthulhuDen\DockerRegistryV2\Authorization;

class Challenge
{
    private $endpoint;
    private $service;
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
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @return self
     */
    public function withScopes(string ...$scopes): self
    {
        $clone = clone $this;
        $clone->scopes = $scopes;

        return $clone;
    }
}
