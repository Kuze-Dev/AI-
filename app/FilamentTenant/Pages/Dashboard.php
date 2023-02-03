<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages;

use App\FilamentTenant\Widgets\AccountWidget;
use Artificertech\FilamentMultiContext\Concerns\ContextualPage;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\FilamentInfoWidget;

class Dashboard extends BaseDashboard
{
    use ContextualPage;

    protected static string $view = 'filament.pages.dashboard';

    protected function getWidgets(): array
    {
        return [
            AccountWidget::class,
            FilamentInfoWidget::class,
        ];
    }
}
