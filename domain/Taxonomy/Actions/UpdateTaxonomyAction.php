<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Domain\Taxonomy\Models\Taxonomy;

class UpdateTaxonomyAction
{
    public function __construct(
        protected SyncTermTreeAction $syncTermAction,
    ) {
    }

    public function execute(Taxonomy $taxonomy, TaxonomyData $taxonomyData): Taxonomy
    {
        $taxonomy->update([
            'name' => $taxonomyData->name,
        ]);

        $this->syncTermAction->execute($taxonomy, $taxonomyData->terms);

        return $taxonomy;
    }
}
