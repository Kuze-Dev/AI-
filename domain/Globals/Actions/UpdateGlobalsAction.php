<?php

declare(strict_types=1);

namespace Domain\Globals\Actions;

use Domain\Globals\DataTransferObjects\GlobalsData;
use Domain\Globals\Models\Globals;
use Illuminate\Support\Facades\Auth;

class UpdateGlobalsAction
{
    /**
     * Execute operations for updating
     * collection and save collection query.
     */
    public function execute(Globals $globals, GlobalsData $globalData): Globals
    {
        $globals->update([
            'name' => $globalData->name,
            'data' => $globalData->data,
        ]);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) &&
            Auth::user()?->hasRole(config('domain.role.super_admin'))
        ) {

            $globals->sites()
                ->sync($globalData->sites);

        }

        return $globals;
    }
}
