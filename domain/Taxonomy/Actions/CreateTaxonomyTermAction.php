<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Taxonomy\DataTransferObjects\TaxonomyTermData;
use Domain\Taxonomy\Models\TaxonomyTerm;

class CreateTaxonomyTermAction
{
    public function execute(TaxonomyTermData $taxonomyData): TaxonomyTerm
    {
        return TaxonomyTerm::create([
            'taxonomy_id' => $taxonomyData->taxonomy_id,
            'name' => $taxonomyData->name,
            'slug' => $taxonomyData->slug,
        ]);
    }
}
