<?php

declare(strict_types=1);

namespace Domain\Collection\DataTransferObjects;

use Carbon\Carbon;
use Domain\Support\MetaData\DataTransferObjects\MetaDataData;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;

class CollectionEntryData
{
    public function __construct(
        public readonly string $title,
        public readonly MetaDataData $meta_data,
        public readonly RouteUrlData $route_url_data,
        public readonly array $taxonomy_terms = [],
        public readonly ?Carbon $published_at = null,
        public readonly array $data = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            route_url_data: new RouteUrlData(url: $data['route_url']['url'] ?? null),
            published_at: isset($data['published_at']) ? Carbon::parse($data['published_at']) : null,
            taxonomy_terms: $data['taxonomy_terms'] ?? [],
            data: $data['data'],
            meta_data: MetaDataData::fromArray($data['meta_data'])
        );
    }
}
