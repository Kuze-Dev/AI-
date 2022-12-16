<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Taxonomy\DataTransferObjects\TaxonomyTermData;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;

class CreateTaxonomyTermAction
{
    public function execute(Taxonomy $taxonomy, TaxonomyTermData $taxonomyData): TaxonomyTerm
    {
        return $taxonomy->taxonomyTerms()->create([
            'name' => $taxonomyData->name,
            'slug' => $taxonomyData->slug,
            'description' => $taxonomyData->description,
        ]);
    }
}
