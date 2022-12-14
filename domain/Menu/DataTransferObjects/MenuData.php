<?php

declare(strict_types=1);

namespace Domain\Menu\DataTransferObjects;

class MenuData
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
    ) {
    }
}
