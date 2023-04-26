<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;

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
        ]);

        $this->syncNodeAction->execute($menu, $menuData->nodes);

        return $menu;
    }
}
