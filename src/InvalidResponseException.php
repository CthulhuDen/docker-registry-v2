<?php

namespace CthulhuDen\DockerRegistryV2;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class InvalidResponseException extends \Exception
{
    private $response;

    public function __construct($message, ResponseInterface $response, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function __toString(): string
    {
        return parent::__toString() . "\n\nResponse body:\n{$this->response->getBody()}";
    }
}
