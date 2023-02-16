<?php

declare(strict_types=1);

namespace Domain\Collection\DataTransferObjects;

use Carbon\Carbon;
use Domain\Support\MetaData\DataTransferObjects\MetaDataData;

class CollectionEntryData
{
    public function __construct(
        public readonly string $title,
        public readonly MetaDataData $meta_data,
        public readonly ?string $slug = null,
        public readonly array $taxonomy_terms = [],
        public readonly ?Carbon $published_at = null,
        public readonly array $data = [],
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
            meta_data: new MetaDataData(
                title: $data['meta_data']['title'],
                author: $data['meta_data']['author'],
                description: $data['meta_data']['description'],
                keywords: $data['meta_data']['keywords'],
            )
        );
    }
}
