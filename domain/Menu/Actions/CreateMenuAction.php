<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;

class CreateMenuAction
{
    public function __construct(
        protected CreateNodeAction $createNodeAction
    ) {
    }

    public function execute(MenuData $menuData): Menu
    {
        $menu = Menu::create([
            'name' => $menuData->name,
            'slug' => $menuData->slug,
        ]);

        if ( ! empty($menuData->nodes)) {
            foreach ($menuData->nodes ?? [] as $nodeData) {
                $this->createNodeAction->execute($menu, $nodeData);
            }
        }

        return $menu;
    }
}
