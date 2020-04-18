<?php

namespace CthulhuDen\DockerRegistryV2\Authorization;

use CthulhuDen\DockerRegistryV2\Authorization\Exception\InvalidChallengeException;

interface ChallengeParserInterface
{
    /**
     * @throws InvalidChallengeException
     */
    public function parse(string $wwwAuthentication): Challenge;
}
