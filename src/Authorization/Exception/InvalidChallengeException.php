<?php

namespace CthulhuDen\DockerRegistryV2\Authorization\Exception;

use CthulhuDen\DockerRegistryV2\Authorization\Exception\AuthorizationException;
use Throwable;

class InvalidChallengeException extends AuthorizationException
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('WWW-Authenticate header could not be parsed', $previous);
    }
}
