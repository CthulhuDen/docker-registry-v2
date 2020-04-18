<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Exception;

use Psr\Http\Message\ResponseInterface;

class InvalidAuthorizationResponseException extends AuthorizationException
{
    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(string $details, ResponseInterface $response)
    {
        $this->response = $response;

        parent::__construct($details);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
