<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;

class CreateMenuAction
{
    public function execute(MenuData $menuData): Menu
    {
        $menu = Menu::create([
            'name' => $menuData->name,
            'locale' => $menuData->locale,
        ]);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)
        ) {
            $menu->sites()
                ->attach($menuData->sites);
        }

        return $menu;
    }
}
