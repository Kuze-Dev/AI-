<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\FilamentTenant\Middleware\Authenticate;
use App\FilamentTenant\Widgets\DeployStaticSite;
use App\Settings\SiteSettings;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
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
            ->authGuard('admin')
            ->login()
            ->brandName(fn () => app(SiteSettings::class)->name)
            ->discoverResources(in: app_path('FilamentTenant/Resources'), for: 'App\\FilamentTenant\\Resources')
            ->discoverPages(in: app_path('FilamentTenant/Pages'), for: 'App\\FilamentTenant\\Pages')
//            ->discoverWidgets(in: app_path('FilamentTenant/Widgets'), for: 'App\\FilamentTenant\\Widgets')
            ->discoverClusters(in: app_path('FilamentTenant/Clusters'), for: 'App\\FilamentTenant\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                DeployStaticSite::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()->label(trans('Shop Configuration')),
                NavigationGroup::make()->label(trans('Customer Management')),
                NavigationGroup::make()->label(trans('CMS')),
                NavigationGroup::make()->label(trans('eCommerce')),
                NavigationGroup::make()->label(trans('Access')),
                NavigationGroup::make()->label(trans('System')),
            ])
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->unsavedChangesAlerts()
            ->maxContentWidth(MaxWidth::Full)
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
            ->middleware(
                [
                    'universal',
                    'tenant',
                ],
                isPersistent: true
            )
            ->authMiddleware([
                Authenticate::class,
                'verified:filament.auth.verification.notice',
                'active:filament.auth.account-deactivated.notice',
            ]);
    }
}
