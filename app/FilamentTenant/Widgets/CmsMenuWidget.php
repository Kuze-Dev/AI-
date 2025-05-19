<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;

class CmsMenuWidget extends BaseAdminMenuWidget
{
    protected static ?int $sort = -1;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int | string | array $columnSpan = 2;
    

    public static function canView(): bool
    {
        return true;
    }

    protected static string $view = 'filament.widgets.menu-nav-widget';


}
