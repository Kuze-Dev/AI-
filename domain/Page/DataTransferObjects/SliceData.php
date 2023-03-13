<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

class SliceData
{
    public function __construct(
        public readonly string $name,
        public readonly string $component,
        public readonly string $image,
        public readonly string $blueprint_id,
        public readonly bool $is_fixed_content,
        public readonly ?array $data = null,
    ) {
    }
}
