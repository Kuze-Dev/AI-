<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets\Widget;

class BaseAdminMenuWidget extends Widget
{

    public static function canView(): bool
    {
        return true;
    }

    public function label(): string
    {
        return 'CMS';
    }

    protected static string $view = 'filament.widgets.menu-nav-widget';

    public function getNavigationByGroup(): NavigationGroup
    {
        return Filament::getNavigation()['CMS'];
    }

    public function getNavigationItems(): array
    {
        return Filament::getNavigation(); // returns all nav groups and items
    }

    protected function getViewData(): array
    {
        return [
            'navigationItems' => $this->getNavigationItems(),
            'navigationGroup' => $this->getNavigationByGroup(),
        ];
    }
}
