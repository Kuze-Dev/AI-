<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

use Domain\Support\MetaData\DataTransferObjects\MetaDataData;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;

class PageData
{
    public function __construct(
        public readonly string $name,
        public readonly RouteUrlData $route_url_data,
        public readonly MetaDataData $meta_data,
        public readonly array $block_contents = [],
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
            route_url_data: RouteUrlData::fromArray($data),
            meta_data: MetaDataData::fromArray($data['meta_data']),
        );
    }
}
