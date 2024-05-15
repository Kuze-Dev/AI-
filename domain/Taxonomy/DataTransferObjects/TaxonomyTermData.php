<?php

declare(strict_types=1);

namespace Domain\Taxonomy\DataTransferObjects;

use Support\RouteUrl\DataTransferObjects\RouteUrlData;

class TaxonomyTermData
{
    public function __construct(
        public readonly string $name,
        public readonly array $data,
        public readonly RouteUrlData $route_url_data,
        public readonly ?int $id = null,
        public readonly ?array $children = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            data: $data['data'],
            route_url_data: RouteUrlData::fromArray($data['route_url'] ?? []),
            id: $data['id'] ?? null,
            children: array_map(fn (array $child) => self::fromArray($child), $data['children'] ?? [])
        );
    }
}
