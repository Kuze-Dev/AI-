<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\FilamentTenant\Widgets\AccessMenuWidget;
use App\FilamentTenant\Widgets\CmsMenuWidget;
use App\FilamentTenant\Widgets\SystemAdminMenuWidget;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class CustomThemeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component('app.filament-tenant.widgets.cms-widget', CmsMenuWidget::class);
        // Livewire::component('app.filament-tenant.widgets.access-menu-widget', AccessMenuWidget::class);
        // Livewire::component('app.filament-tenant.widgets.system-admin-menu-widget', SystemAdminMenuWidget::class);

    }
}
