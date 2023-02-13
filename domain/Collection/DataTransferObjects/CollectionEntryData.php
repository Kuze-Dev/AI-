<?php

declare(strict_types=1);

namespace Domain\Collection\DataTransferObjects;

use Carbon\Carbon;
use Domain\Support\MetaTag\DataTransferObjects\MetaTagData;

class CollectionEntryData
{
    public function __construct(
        public readonly MetaTagData $meta_tags,
        public readonly string $title,
        public readonly ?string $slug = null,
        public readonly array $taxonomy_terms = [],
        public readonly ?Carbon $published_at = null,
        public readonly array $data = [],
    ) {
    }
}
