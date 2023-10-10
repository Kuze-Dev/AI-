<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;
use Illuminate\Support\Facades\Auth;

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

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) &&
        Auth::user()?->hasRole(config('domain.role.super_admin'))
        ) {

            $menu->sites()
                ->sync($menuData->sites);

        }

        return $menu;
    }
}
