<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Taxonomy\DataTransferObjects\TaxonomyTermData;
use Domain\Taxonomy\Models\TaxonomyTerm;

class UpdateTaxonomyTermAction
{
    public function execute(TaxonomyTerm $taxonomyTerm, TaxonomyTermData $taxonomyTermData): TaxonomyTerm
    {
        $taxonomyTerm->update([
            'name' => $taxonomyTermData->name,
            'description' => $taxonomyTermData->description,
        ]);

        return $taxonomyTerm;
    }
}
