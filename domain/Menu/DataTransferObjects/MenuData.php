<?php

declare(strict_types=1);

namespace Domain\Menu\DataTransferObjects;

class MenuData
{
    /** @param \Domain\Menu\DataTransferObjects\NodeData[] $nodes */
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug = null,
        public readonly array $nodes = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'] ?? null,
            nodes: array_map(fn (array $nodeData) => NodeData::fromArray($nodeData), $data['nodes'] ?? []),
        );
    }
}
