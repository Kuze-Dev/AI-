<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use App\Features\CMS\SitesManagement;
use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;
<<<<<<< HEAD
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Support\Facades\Auth;
=======
>>>>>>> develop

class UpdateMenuAction
{
    public function __construct(
        protected SyncNodeTreeAction $syncNodeAction,
    ) {
    }

    public function execute(Menu $menu, MenuData $menuData): Menu
    {
        $menu->update([
            'name' => $menuData->name,
            'locale' => $menuData->locale,
        ]);

        $this->syncNodeAction->execute($menu, $menuData->nodes);

<<<<<<< HEAD
        if (TenantFeatureSupport::active(SitesManagement::class) &&
        Auth::user()?->hasRole(config('domain.role.super_admin'))
        ) {
=======
        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)) {
>>>>>>> develop

            $menu->sites()->sync($menuData->sites);

        }

        return $menu;
    }
}
