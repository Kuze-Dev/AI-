<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;

class CreateMenuAction
{
    public function __construct(
        protected SyncNodeTreeAction $syncNodeAction,
    ) {
    }
   
    public function execute(MenuData $menuData): Menu
    {
        $menu = Menu::create([
            'name' => $menuData->name,
            'locale' => $menuData->locale,
        ]);

        $this->syncNodeAction->execute($menu, $menuData->nodes);

        if (\Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)
        ) {
            $menu->sites()
                ->attach($menuData->sites);
        }

        return $menu;
    }
}
