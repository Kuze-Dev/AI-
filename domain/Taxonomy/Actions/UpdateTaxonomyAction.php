<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Domain\Taxonomy\Models\Taxonomy;

class UpdateTaxonomyAction
{
    public function execute(Taxonomy $taxonomy, TaxonomyData $pageData): Taxonomy
    {
        $taxonomy->fill([
            'name' => $pageData->name,
        ]);

        $taxonomy->save();

        return $taxonomy;
    }
}
