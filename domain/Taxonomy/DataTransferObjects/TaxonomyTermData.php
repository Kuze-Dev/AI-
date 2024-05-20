<?php

declare(strict_types=1);

namespace Domain\Taxonomy\DataTransferObjects;

class TaxonomyTermData
{
    public function __construct(
        public readonly string $name,
        public readonly array $data,
        public readonly string $url,
        public readonly ?int $id = null,
        public readonly ?array $children = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            data: $data['data'],
            url: $data['url'],
            id: $data['id'] ?? null,
            children: array_map(fn (array $child) => self::fromArray($child), $data['children'] ?? [])
        );
    }
}
