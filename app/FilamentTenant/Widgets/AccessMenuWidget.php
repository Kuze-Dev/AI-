<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;

class AccessMenuWidget extends BaseAdminMenuWidget
{

    public static function canView(): bool
    {
        return true;
    }

    public function label(): string
    {
        return 'Access';
    }

    protected static string $view = 'filament.widgets.menu-nav-widget';

    public function getNavigationByGroup(): NavigationGroup
    {
        return Filament::getNavigation()['Access'];
    }
}
