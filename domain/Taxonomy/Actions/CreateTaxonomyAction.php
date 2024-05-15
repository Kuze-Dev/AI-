<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Domain\Taxonomy\Models\Taxonomy;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class CreateTaxonomyAction
{
    public function __construct(
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
    ) {
    }

    public function execute(TaxonomyData $taxonomyData): Taxonomy
    {
        $taxonomy = Taxonomy::create([
            'name' => $taxonomyData->name,
            'blueprint_id' => $taxonomyData->blueprint_id,
        ]);

        $this->createOrUpdateRouteUrl->execute($taxonomy, $taxonomyData->route_url_data);

        return $taxonomy;
    }
}
