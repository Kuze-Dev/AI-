<?php

declare(strict_types=1);

namespace Domain\Content\DataTransferObjects;

use Domain\Content\Enums\PublishBehavior;

readonly class ContentData
{
    public function __construct(
        public string $name,
        public string $blueprint_id,
        public string $prefix,
        public string $visibility,
        public array $taxonomies = [],
        public ?PublishBehavior $past_publish_date_behavior = null,
        public ?PublishBehavior $future_publish_date_behavior = null,
        public bool $is_sortable = false,
        public array $sites = [],
    ) {
    }
}
