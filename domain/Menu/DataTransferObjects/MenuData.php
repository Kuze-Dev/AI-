<?php

declare(strict_types=1);

namespace Domain\Menu\DataTransferObjects;

readonly class MenuData
{
    /** @param  \Domain\Menu\DataTransferObjects\NodeData[]  $nodes */
    public function __construct(
        public string $name,
        public array $nodes = [],
        public array $sites = [],
        public ?string $locale = 'en',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            nodes: array_map(fn (array $nodeData) => NodeData::fromArray($nodeData), $data['nodes'] ?? []),
            sites: $data['sites'] ?? [],
            locale: $data['locale'] ?? 'en',
        );
    }
}
