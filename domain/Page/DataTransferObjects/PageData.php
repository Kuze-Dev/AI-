<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

use Domain\Page\Enums\PageBehavior;

class PageData
{
    public function __construct(
        public readonly string $name,
        public readonly int $blueprint_id,
        public readonly ?PageBehavior $past_behavior = null,
        public readonly ?PageBehavior $future_behavior = null,
    ) {
    }
}
