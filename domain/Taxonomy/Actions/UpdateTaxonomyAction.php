<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Domain\Taxonomy\Models\Taxonomy;

class UpdateTaxonomyAction
{
    public function execute(Taxonomy $taxonomy, TaxonomyData $taxonomyData): Taxonomy
    {
        $taxonomy->fill([
            'name' => $taxonomyData->name,
        ]);

        $taxonomy->save();

        return $taxonomy;
    }
}
