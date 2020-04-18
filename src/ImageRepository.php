<?php

namespace CthulhuDen\DockerRegistryV2;

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

    public function withTag(string $tag): ImageId
    {
        return new ImageId($this->getName(), $tag);
    }
}
