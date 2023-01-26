<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\Models\Menu;

class DeleteMenuAction
{
    public function execute(Menu $menu): ?bool
    {
        return $menu->delete();
    }
}
