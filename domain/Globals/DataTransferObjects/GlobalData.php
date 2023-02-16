<?php

declare(strict_types=1);

namespace Domain\Globals\DataTransferObjects;


class GlobalsData
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $blueprint_id,
        public readonly ?array $data = null,

    ) {
    }
}
