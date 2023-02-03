<?php

declare(strict_types=1);

namespace Domain\Taxonomy\DataTransferObjects;

class TaxonomyData
{
    /** @param \Domain\Taxonomy\DataTransferObjects\TaxonomyTermData[] $terms */
    public function __construct(
        public readonly string $name,
        public readonly ?string $slug = null,
        public readonly array $terms = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            terms: array_map(fn (array $termDate) => TaxonomyTermData::fromArray($termDate), $data['terms'] ?? []),
        );
    }
}
