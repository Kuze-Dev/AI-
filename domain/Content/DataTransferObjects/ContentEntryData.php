<?php

declare(strict_types=1);

namespace Domain\Content\DataTransferObjects;

use Carbon\Carbon;
use Domain\Support\MetaData\DataTransferObjects\MetaDataData;

class ContentEntryData
{
    public function __construct(
        public readonly string $title,
        public readonly MetaDataData $meta_data,
        public readonly ?string $slug = null,
        public readonly array $taxonomy_terms = [],
        public readonly ?Carbon $published_at = null,
        public readonly array $data = [],
        public readonly ?int $author_id = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            slug: $data['slug'],
            published_at: isset($data['published_at']) ? Carbon::parse($data['published_at']) : null,
            taxonomy_terms: $data['taxonomy_terms'] ?? [],
            data: $data['data'],
            author_id: auth()->user()->id ?? null,
            meta_data: MetaDataData::fromArray($data['meta_data'])
        );
    }
}
