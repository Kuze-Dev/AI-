<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Domain\Taxonomy\Models\Taxonomy;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class UpdateTaxonomyAction
{
    public function __construct(
        protected SyncTermTreeAction $syncTermAction,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
    ) {
    }

    public function execute(Taxonomy $taxonomy, TaxonomyData $taxonomyData): Taxonomy
    {
        $taxonomy->update([
            'name' => $taxonomyData->name,
        ]);

        $this->syncTermAction->execute($taxonomy, $taxonomyData->terms);

        $this->createOrUpdateRouteUrl->execute($taxonomy, $taxonomyData->route_url_data);
        
        return $taxonomy;
    }
}
