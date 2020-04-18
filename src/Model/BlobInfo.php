<?php

namespace CthulhuDen\DockerRegistryV2\Model;

class BlobInfo
{
    private const TYPE_LAYER = 'application/vnd.docker.image.rootfs.diff.tar.gzip';
    private const TYPE_CONFIG = 'application/vnd.docker.container.image.v1+json';

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $digest;

    public function __construct(string $type, int $size, string $digest)
    {
        switch ($type) {
            case self::TYPE_LAYER:
            case self::TYPE_CONFIG:
                break;
            default:
                throw new \Exception('Unknown blob media type: ' . $type);
        }

        $this->type = $type;
        $this->size = $size;
        $this->digest = $digest;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getDigest(): string
    {
        return $this->digest;
    }
}
