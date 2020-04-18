<?php

namespace CthulhuDen\DockerRegistryV2\Model;

class ImageRepository
{
    private $name;

    public function __construct(string $imageName)
    {
        $this->name = $imageName;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
