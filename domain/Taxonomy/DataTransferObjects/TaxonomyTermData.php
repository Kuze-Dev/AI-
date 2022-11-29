<?php

declare(strict_types=1);

namespace Domain\Taxonomy\DataTransferObjects;

class TaxonomyTermData
{
    public function __construct(
        public readonly int $taxonomy_id,
        public readonly string $name,
        public readonly ?string $slug = null,
    ) {
    }
}
