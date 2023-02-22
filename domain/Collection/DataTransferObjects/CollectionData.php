<?php

declare(strict_types=1);

namespace Domain\Collection\DataTransferObjects;

use Domain\Collection\Enums\PublishBehavior;

class CollectionData
{
    public function __construct(
        public readonly string $name,
        public readonly string $blueprint_id,
        public readonly string $route_url,
        public readonly array $taxonomies = [],
        public readonly ?string $slug = null,
        public readonly ?PublishBehavior $past_publish_date_behavior = null,
        public readonly ?PublishBehavior $future_publish_date_behavior = null,
        public readonly bool $is_sortable = false,
    ) {
    }
}
