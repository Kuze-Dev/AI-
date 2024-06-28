<?php

declare(strict_types=1);

namespace Domain\Globals\Actions;

use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;
use Illuminate\Support\Facades\Auth;

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
            'blueprint_id' => $globalData->blueprint_id,
            'data' => $globalData->data,
        ]);

        $this->createBlueprintDataAction->execute($globals);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) &&
            Auth::user()?->hasRole(config('domain.role.super_admin'))
        ) {

            $globals->sites()
                ->attach($globalData->sites);

        }

        return $globals;
    }
}
