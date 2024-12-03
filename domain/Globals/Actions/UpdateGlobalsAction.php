<?php

declare(strict_types=1);

namespace Domain\Globals\Actions;

<<<<<<< HEAD
use App\Features\CMS\SitesManagement;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Support\Facades\Auth;
=======
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Traits\SanitizeBlueprintDataTrait;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;
use Domain\Internationalization\Actions\HandleUpdateDataTranslation;
>>>>>>> develop

class UpdateGlobalsAction
{
    use SanitizeBlueprintDataTrait;

    public function __construct(
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

    /**
     * Execute operations for updating
     * collection and save collection query.
     */
    public function execute(Globals $globals, GlobalsData $globalData): Globals
    {
        /** @var Blueprint|null */
        $blueprint = Blueprint::whereId($globals->blueprint_id)->first();

        if (! $blueprint) {
            abort(422, 'Cannot Access Blueprint '.$globals->blueprint_id);
        }

        $sanitizeData = $this->sanitizeBlueprintData(
            $globalData->data,
            $blueprint->schema->getFieldStatekeys()
        );

        $globals->update([
            'name' => $globalData->name,
            'data' => $sanitizeData,
        ]);

<<<<<<< HEAD
        if (TenantFeatureSupport::active(SitesManagement::class) &&
            Auth::user()?->hasRole(config('domain.role.super_admin'))
=======
        /** @var Globals */
        $model = Globals::with('blueprint')->where('id', $globals->id)->first();

        $this->updateBlueprintDataAction->execute($model);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)
>>>>>>> develop
        ) {

            $globals->sites()
                ->sync($globalData->sites);

        }

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\Internationalization::class)) {

            app(HandleUpdateDataTranslation::class)->execute($globals, $globalData);

            return $globals;
        }

        return $globals;
    }
}
