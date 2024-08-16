<?php

declare(strict_types=1);

namespace Domain\Content\DataTransferObjects;

use Illuminate\Support\Carbon;
use Support\MetaData\DataTransferObjects\MetaDataData;
use Support\RouteUrl\DataTransferObjects\RouteUrlData;

class ContentEntryData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $locale,
        public readonly MetaDataData $meta_data,
        public readonly RouteUrlData $route_url_data,
        public readonly array $taxonomy_terms = [],
        public readonly bool $published_draft = false,
        public readonly bool $status = false,
        public readonly ?Carbon $published_at = null,
        public readonly array $data = [],
        public readonly ?int $author_id = null,
        public readonly array $sites = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            locale: $data['locale'] ?? null,
            route_url_data: RouteUrlData::fromArray($data['route_url'] ?? []),
            published_at: isset($data['published_at']) ? Carbon::parse($data['published_at']) : null,
            taxonomy_terms: $data['taxonomy_terms'] ?? [],
            data: $data['data'],
            author_id: $data['author_id'] ?? null,
            meta_data: MetaDataData::fromArray($data['meta_data']),
            published_draft: $data['published_draft'] ?? false,
            status: $data['status'] ?? false,
            sites: $data['sites'] ?? [],
        );
    }
}
