<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;
use Illuminate\Support\Facades\Auth;

class CreateMenuAction
{
    public function execute(MenuData $menuData): Menu
    {
        $menu = Menu::create([
            'name' => $menuData->name,
        ]);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) &&
        Auth::user()?->hasRole(config('domain.role.super_admin'))
        ) {
            $menu->sites()
                ->attach($menuData->sites);
        }

        return $menu;
    }
}
