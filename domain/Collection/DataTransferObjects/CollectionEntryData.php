<?php

declare(strict_types=1);

namespace Domain\Collection\DataTransferObjects;

use Carbon\Carbon;
use Domain\Support\MetaData\DataTransferObjects\MetaDataData;

class CollectionEntryData
{
    public function __construct(
        public readonly MetaDataData $meta_data,
        public readonly string $title,
        public readonly ?string $slug = null,
        public readonly array $taxonomy_terms = [],
        public readonly ?Carbon $published_at = null,
        public readonly array $data = [],
    ) {
    }
}
