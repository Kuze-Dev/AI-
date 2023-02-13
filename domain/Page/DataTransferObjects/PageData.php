<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

use Domain\Support\MetaData\DataTransferObjects\MetaDataData;

class PageData
{
    public function __construct(
        public readonly string $name,
        public readonly string $route_url,
        public readonly MetaDataData $meta_data,
        public readonly array $slice_contents = [],
        public readonly ?string $slug = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slice_contents: array_map(
                fn (array $sliceContentData) => new SliceContentData(
                    slice_id: $sliceContentData['slice_id'],
                    data: $sliceContentData['data'],
                    id: $sliceContentData['id'] ?? null,
                ),
                $data['slice_contents'] ?? []
            ),
            slug: $data['slug'] ?? null,
            route_url: $data['route_url'],
            meta_data: new MetaDataData(
                title: $data['meta_data']['title'],
                author: $data['meta_data']['author'],
                description: $data['meta_data']['description'],
                keywords: $data['meta_data']['keywords'],
            )
        );
    }
}
