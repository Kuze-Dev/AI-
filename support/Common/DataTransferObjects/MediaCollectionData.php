<?php

declare(strict_types=1);

namespace Support\Common\DataTransferObjects;

class MediaCollectionData
{
    public function __construct(
        public readonly string $collection,
        public readonly array $media = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            collection: $data['collection'],
            media: array_map(
                fn ($media) => MediaData::fromArray($media),
                array_is_list($data['media'] ?? []) ? $data['media'] : [$data['media']]
            ),
        );
    }
}
