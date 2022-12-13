<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;

class UpdateMenuAction
{
    public function execute(Menu $Menu, MenuData $menuData): Menu
    {
        $Menu->update([
            'title' => $menuData->title,
            'schema' => $menuData->schema,
        ]);
        return $Menu;
    }
}
