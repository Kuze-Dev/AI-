<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

class SliceData
{
    public function __construct(
        public readonly string $name,
        public readonly string $component,
        public readonly string $blueprint_id,
    ) {
    }
}
