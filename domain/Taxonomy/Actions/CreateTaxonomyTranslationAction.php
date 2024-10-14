<?php

declare(strict_types=1);

namespace Domain\Taxonomy\Actions;

use Domain\Taxonomy\DataTransferObjects\TaxonomyData;
use Domain\Taxonomy\Models\Taxonomy;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class CreateTaxonomyTranslationAction
{
    public function __construct(
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected SyncTermTreeAction $syncTermAction,
    ) {
    }

    public function execute(Taxonomy $taxonomy, TaxonomyData $taxonomyData): Taxonomy
    {

        $taxonomyTranslation = $taxonomy->dataTranslation()->create([
            'name' => $taxonomyData->name,
            'blueprint_id' => $taxonomyData->blueprint_id,
            'locale' => $taxonomyData->locale,
            'has_route' => $taxonomyData->has_route,
        ]);

        if ($taxonomyData->has_route) {
            $this->createOrUpdateRouteUrl->execute($taxonomyTranslation, $taxonomyData->route_url_data);
        }

        $this->syncTermAction->execute($taxonomyTranslation, $taxonomyData->terms);

        $taxonomyTranslation->sites()->attach($taxonomyData->sites);

        return $taxonomyTranslation;
    }
}
