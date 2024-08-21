<?php

declare(strict_types=1);

namespace Domain\Content\DataTransferObjects;

use Domain\Content\Enums\PublishBehavior;

class ContentData
{
    public function __construct(
        public readonly string $name,
        public readonly string $blueprint_id,
        public readonly string $prefix,
        public readonly string $visibility,
        public readonly array $taxonomies = [],
        public readonly ?PublishBehavior $past_publish_date_behavior = null,
        public readonly ?PublishBehavior $future_publish_date_behavior = null,
        public readonly bool $is_sortable = false,
        public readonly array $sites = [],
    ) {
    }
}
