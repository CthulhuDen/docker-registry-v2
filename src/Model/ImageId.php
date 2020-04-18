<?php

namespace CthulhuDen\DockerRegistryV2\Model;

class ImageId
{
    private $repository;
    private $tag;

    /**
     * @param string|ImageId $repository
     */
    public function __construct($repository, string $tag = 'latest')
    {
        $this->repository = is_string($repository)
            ? new ImageRepository($repository)
            : $repository;

        $this->tag = $tag;
    }

    public function getName(): string
    {
        return $this->repository->getName();
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getRepository(): ImageRepository
    {
        return $this->repository;
    }
}
