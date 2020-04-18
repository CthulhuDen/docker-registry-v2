<?php

namespace CthulhuDen\DockerRegistryV2;

class ImageId
{
    private $name;
    private $tag;

    public function __construct(string $name, string $tag = 'latest')
    {
        $this->name = $name;
        $this->tag = $tag;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getRepository(): ImageRepository
    {
        return new ImageRepository($this->name);
    }
}
