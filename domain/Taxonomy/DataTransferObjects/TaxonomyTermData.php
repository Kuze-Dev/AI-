<?php

declare(strict_types=1);

namespace Domain\Taxonomy\DataTransferObjects;

class TaxonomyTermData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug = null,
        public readonly ?int $id = null,
        public readonly ?string $description = null,
        public readonly ?array $children = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug:$data['slug'] ?? null,
            id: $data['id'] ?? null,
            description: $data['description'] ?? null,
            children: array_map(fn (array $child) => self::fromArray($child), $data['children'] ?? [])
        );
    }
}
