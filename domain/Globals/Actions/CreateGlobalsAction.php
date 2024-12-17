<?php

declare(strict_types=1);

namespace Domain\Globals\Actions;

use App\Features\CMS\SitesManagement;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Support\Facades\Auth;
use Domain\Blueprint\Actions\CreateBlueprintDataAction;


class CreateGlobalsAction
{
    public function __construct(
        protected CreateBlueprintDataAction $createBlueprintDataAction,
    ) {
    }

    /** Execute create collection query. */
    public function execute(GlobalsData $globalData): Globals
    {
        /** @var Globals */
        $globals = Globals::create([
            'name' => $globalData->name,
            'locale' => $globalData->locale,
            'blueprint_id' => $globalData->blueprint_id,
            'data' => $globalData->data,
        ]);


        if (TenantFeatureSupport::active(SitesManagement::class) &&
            Auth::user()?->hasRole(config('domain.role.super_admin'))
        ) {

        $this->createBlueprintDataAction->execute($globals);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)) {

            $globals->sites()
                ->attach($globalData->sites);

        }

        return $globals;
    }
}
