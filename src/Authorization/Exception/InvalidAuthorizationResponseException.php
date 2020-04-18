<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Exception;

use CthulhuDen\DockerRegistryV2\Authorization\Exception\AuthorizationException;
use Psr\Http\Message\ResponseInterface;

class InvalidAuthorizationResponseException extends AuthorizationException
{
    private $response;

    public function __construct(string $details, ResponseInterface $response)
    {
        parent::__construct($details);

        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
