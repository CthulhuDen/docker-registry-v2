<?php

namespace CthulhuDen\DockerRegistryV2\Model;

class Manifest
{
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function __toString(): string
    {
        return $this->content;
    }

    /**
     * @return BlobInfo[]
     */
    public function getLayers(): array
    {
        $data = json_decode($this->content, true);

        $return = [];
        foreach ($data['layers'] as ['mediaType' => $type, 'size' => $size, 'digest' => $digest]) {
            $return[] = new BlobInfo($type, $size, $digest);
        }

        return $return;
    }

    public function getConfig(): BlobInfo
    {
        $data = json_decode($this->content, true)['config'];

        return new BlobInfo($data['mediaType'], $data['size'], $data['digest']);
    }
}
