<?php

namespace CthulhuDen\DockerRegistryV2;

use Psr\Http\Message\ResponseInterface;
use Throwable;

class InvalidResponseException extends \Exception
{
    /** @var ResponseInterface */
    private $response;

    public function __construct(string $message, ResponseInterface $response, Throwable $previous = null)
    {
        $this->response = $response;

        parent::__construct($message, 0, $previous);
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
