<?php

declare(strict_types=1);

namespace Domain\Globals\DataTransferObjects;

class GlobalsData
{
    public function __construct(
        public readonly string $name,
        public readonly string $blueprint_id,
        public readonly array $data = [],
        public readonly array $sites = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            blueprint_id: $data['blueprint_id'],
            data: $data['data'] ?? [],
            sites: $data['sites'],
        );
    }
}
