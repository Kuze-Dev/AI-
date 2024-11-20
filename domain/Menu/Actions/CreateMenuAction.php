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

class CreateMenuAction
{
    public function execute(MenuData $menuData): Menu
    {
        $menu = Menu::create([
            'name' => $menuData->name,
            'locale' => $menuData->locale,
        ]);

<<<<<<< HEAD
        if (TenantFeatureSupport::active(SitesManagement::class) &&
        Auth::user()?->hasRole(config('domain.role.super_admin'))
=======
        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)
>>>>>>> develop
        ) {
            $menu->sites()
                ->attach($menuData->sites);
        }

        return $menu;
    }
}
