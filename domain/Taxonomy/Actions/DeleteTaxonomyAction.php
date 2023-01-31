<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Taxonomy\Models\Taxonomy;

class DeleteTaxonomyAction
{
    public function execute(Taxonomy $taxonomy): ?bool
    {
        return $taxonomy->delete();
    }
}
