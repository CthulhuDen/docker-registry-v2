<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Exception;

use Throwable;

class AuthorizationException extends \Exception
{
    public function __construct(string $details, Throwable $previous = null)
    {
        parent::__construct("Failed to authorize request: {$details}", 0, $previous);
    }
}
