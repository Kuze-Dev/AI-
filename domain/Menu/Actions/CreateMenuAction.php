<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;

class CreateMenuAction
{
    public function execute(MenuData $menuData): Menu
    {
        return Menu::create([
            'name' => $menuData->name,
            'slug' => $menuData->slug,
        ]);
    }
}
