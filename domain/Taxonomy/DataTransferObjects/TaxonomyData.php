<?php

declare(strict_types=1);

namespace Domain\Taxonomy\DataTransferObjects;

use Support\RouteUrl\DataTransferObjects\RouteUrlData;

class TaxonomyData
{
    /** @param  \Domain\Taxonomy\DataTransferObjects\TaxonomyTermData[]  $terms */
    public function __construct(
        public readonly string $name,
        public readonly RouteUrlData $route_url_data,
        public readonly string $blueprint_id,
        public readonly string $locale,
        public readonly array $terms = [],
        public readonly array $sites = [],
        public readonly bool $has_route = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            route_url_data: RouteUrlData::fromArray($data['route_url'] ?? []),
            blueprint_id: $data['blueprint_id'],
            terms: array_map(fn (array $termDate) => TaxonomyTermData::fromArray($termDate), $data['terms'] ?? []),
            sites: $data['sites'] ?? [],
            locale: $data['locale'] ?? 'en',
            has_route: $data['has_route'] ?? false,
        );
    }
}
