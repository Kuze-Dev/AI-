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
        public readonly array $slice_contents = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slice_contents: array_map(
                fn (array $sliceContentData) => new SliceContentData(
                    slice_id: $sliceContentData['slice_id'],
                    data: $sliceContentData['data'] ?? null,
                    id: $sliceContentData['id'] ?? null,
                ),
                $data['slice_contents'] ?? []
            ),
            route_url_data: new RouteUrlData(url: $data['route_url']['url'] ?? null),
            meta_data: MetaDataData::fromArray($data['meta_data'])
        );
    }
}
