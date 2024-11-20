<?php

declare(strict_types=1);

namespace Domain\Menu\Actions;

use Domain\Menu\DataTransferObjects\MenuData;
use Domain\Menu\Models\Menu;

class CreateMenuTranslationAction
{
    public function __construct(
        protected SyncNodeTreeAction $syncNodeAction,
    ) {
    }

    public function execute(Menu $menu, MenuData $menuData): Menu
    {

        $menuTranslation = $menu->dataTranslation()->create([
            'name' => $menuData->name,
            'locale' => $menuData->locale,
        ]);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)
        ) {
            $menuTranslation->sites()
                ->attach($menuData->sites);
        }

        $this->syncNodeAction->execute($menuTranslation, $menuData->nodes);

        return $menuTranslation;
    }
}
