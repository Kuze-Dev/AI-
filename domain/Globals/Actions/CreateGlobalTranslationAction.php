<?php

declare(strict_types=1);

namespace Domain\Globals\Actions;

use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Content\Models\Content;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;
use Domain\Internationalization\Actions\HandleDataTranslation;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class CreateGlobalTranslationAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected CreateBlueprintDataAction $createBlueprintDataAction,
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

    /** Execute create content entry query. */
    public function execute(Globals $global, GlobalsData $globalData): Globals
    {
        /** @var Globals $globalTranslation */
        $globalTranslation = $global->dataTranslation()
            ->create([
                'name' => $globalData->name,
                'blueprint_id' => $globalData->blueprint_id,
                'locale' => $globalData->locale,
                'data' => $globalData->data,
            ]);

        $this->createBlueprintDataAction->execute($globalTranslation);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)) {

            $globalTranslation->sites()->sync($globalData->sites);
        }

        app(HandleDataTranslation::class)->execute($global, $globalTranslation);

        $globalTranslation->refresh();

        return $globalTranslation;

    }
}
