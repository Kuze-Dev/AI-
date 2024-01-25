<?php

namespace App\Providers\Filament;

use App\FilamentTenant\Middleware\Authenticate;
use App\FilamentTenant\Widgets\DeployStaticSite;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TenantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tenant')
            ->path('admin')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->login()
            ->authGuard('admin')
            ->brandName(fn() => tenant()->name)
            ->discoverResources(in: app_path('FilamentTenant/Resources'), for: 'App\\FilamentTenant\\Resources')
            ->discoverPages(in: app_path('FilamentTenant/Pages'), for: 'App\\FilamentTenant\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
//            ->discoverWidgets(in: app_path('FilamentTenant/Widgets'), for: 'App\\FilamentTenant\\Widgets')
            ->widgets([
                AccountWidget::class,
                DeployStaticSite::class,
            ])
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->middleware([
                'universal',
                'tenant',
            ],
                isPersistent:true
            )
            ->authMiddleware([
                Authenticate::class,
                'verified:filament.auth.verification.notice',
                'active:filament.auth.account-deactivated.notice',
            ]);
    }
}
