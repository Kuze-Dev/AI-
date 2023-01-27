<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Dashboard;

use App\FilamentTenant\Widgets\AccountWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\FilamentInfoWidget;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    protected function getWidgets(): array
    {
        return [
            AccountWidget::class,
            FilamentInfoWidget::class
        ];
    }
}
