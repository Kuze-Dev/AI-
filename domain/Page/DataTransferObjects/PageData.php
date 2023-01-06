<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

class PageData
{
    public function __construct(
        public readonly string $name,
        public readonly string $blueprint_id,
        public readonly ?string $slug = null,
    ) {
    }
}
