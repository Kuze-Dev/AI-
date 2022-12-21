<?php

declare(strict_types=1);

namespace Domain\Collection\DataTransferObjects;

class CollectionData
{
    public function __construct(
        public readonly string $name,
        public readonly ?int $blueprint_id = null,
        public readonly ?int $taxonomy_id = null,
        public readonly ?string $slug = null,
        public readonly ?string $past_publish_date_behavior = null,
        public readonly ?string $future_publish_date_behavior = null,
        public readonly ?bool $is_sortable,
    ) {
    }
}
