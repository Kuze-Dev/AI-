<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

use Domain\Support\MetaData\DataTransferObjects\MetaDataData;
use Carbon\Carbon;

class PageData
{
    public function __construct(
        public readonly string $name,
        public readonly string $route_url,
        public readonly MetaDataData $meta_data,
        public readonly array $block_contents = [],
        public readonly ?string $slug = null,
        public readonly ?Carbon $published_at = null,
        public readonly ?int $author_id = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            block_contents: array_map(
                fn (array $blockContentData) => new BlockContentData(
                    block_id: $blockContentData['block_id'],
                    data: $blockContentData['data'] ?? null,
                    id: $blockContentData['id'] ?? null,
                ),
                $data['block_contents'] ?? []
            ),
            slug: $data['slug'] ?? null,
            route_url: $data['route_url'],
            published_at: isset($data['published_at']) ? Carbon::parse($data['published_at']) : null,
            author_id: $data['author_id'] ?? null,
            meta_data: MetaDataData::fromArray($data['meta_data'])
        );
    }
}
