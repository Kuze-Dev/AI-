<?php

declare(strict_types=1);

namespace Domain\Menu\DataTransferObjects;

class MenuData
{
    /** @param \Domain\Menu\DataTransferObjects\NodeData[] $nodes */
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly array $nodes = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            nodes: array_map(
                fn (array $nodeData) => new NodeData(
                    id: $nodeData['id'] ?? null,
                    label: $nodeData['label'],
                    menu_id: $nodeData['menu_id'] ?? null,
                    parent_id: $nodeData['parent_id'] ?? null,
                    sort: $nodeData['sort'] ?? null,
                    url: $nodeData['url'] ?? null,
                    target: $nodeData['target'],
                ),
                $data['nodes'] ?? []
            ),
        );
    }
}
