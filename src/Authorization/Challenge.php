<?php

namespace CthulhuDen\DockerRegistryV2\Authorization;

class Challenge
{
    private $endpoint;

    private $parameters;

    public function __construct(string $endpoint, array $parameters)
    {
        $this->endpoint = $endpoint;
        $this->parameters = $parameters;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
