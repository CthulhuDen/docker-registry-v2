<?php

/**
 * @psalm-type Blob=array{mediaType:string,size:int,digest:string}
 */

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
     * @return array<int, BlobInfo>
     * @psalm-return list<BlobInfo>
     */
    public function getLayers(): array
    {
        /** @psalm-var array{layers:list<Blob>} $data */
        $data = json_decode($this->content, true);

        $return = [];
        foreach ($data['layers'] as ['mediaType' => $type, 'size' => $size, 'digest' => $digest]) {
            $return[] = new BlobInfo($type, $size, $digest);
        }

        return $return;
    }

    public function getConfig(): BlobInfo
    {
        /** @psalm-var array{config:Blob} $data */
        $data = json_decode($this->content, true);

        $blob = $data['config'];

        return new BlobInfo($blob['mediaType'], $blob['size'], $blob['digest']);
    }
}
