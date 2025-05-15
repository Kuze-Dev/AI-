<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class CmsWidget extends Widget
{

    public static function canView(): bool
    {
        return true;
    }

    
    protected static string $view = 'filament.widgets.cmswidget';

    public function getNavigationItems(): array
    {
        return Filament::getNavigation(); // returns all nav groups and items
    }

    protected function getViewData(): array
    {
        return [
            'navigationItems' => $this->getNavigationItems(),
        ];
    }
}
