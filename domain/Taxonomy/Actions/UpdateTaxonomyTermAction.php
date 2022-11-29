<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Taxonomy\DataTransferObjects\TaxonomyTermData;
use Domain\Taxonomy\Models\TaxonomyTerm;

class UpdateTaxonomyTermAction
{
    public function execute(TaxonomyTerm $taxonomyTerm, TaxonomyTermData $taxonomyTermData): TaxonomyTerm
    {
        $taxonomyTerm->fill([
            'name' => $taxonomyTermData->name,
            'taxonomy_id' => $taxonomyTermData->taxonomy_id,
        ]);

        $taxonomyTerm->save();

        return $taxonomyTerm;
    }
}
