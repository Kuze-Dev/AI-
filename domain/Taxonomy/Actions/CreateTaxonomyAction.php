<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Domain\Taxonomy\Models\Taxonomy;

class CreateTaxonomyAction
{
    public function execute(TaxonomyData $taxonomyData): Taxonomy
    {
        return Taxonomy::create([
            'name' => $taxonomyData->name,
            'slug' => $taxonomyData->slug,
        ]);
    }
}
