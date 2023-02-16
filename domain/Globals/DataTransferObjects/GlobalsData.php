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

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            blueprint_id: $data['blueprint_id'],
            data: $data['data'],
        );
    }
}
