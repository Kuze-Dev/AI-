<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\FilamentTenant\Livewire\Auth\TwoFactorAuthentication;
use App\FilamentTenant\Pages\AccountDeactivatedNotice;
use App\FilamentTenant\Widgets\CmsWidget;
use App\FilamentTenant\Widgets\DeployStaticSite;
use App\FilamentTenant\Widgets\Report as ReportWidget;
use App\Settings\SiteSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use JulioMotol\FilamentPasswordConfirmation\FilamentPasswordConfirmationPlugin;

class TenantPanelProvider extends PanelProvider
{
    #[\Override]
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tenant')
            ->path('admin')
            ->authGuard('admin')
            ->authPasswordBroker('admin')
            ->login()
            ->profile()
            ->passwordReset()
            ->emailVerification()
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn () => filament_admin()->full_name),
            ])
            ->colors([
                'primary' => Color::Blue,
            ])
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
                CmsWidget::class,
                ReportWidget\TotalSales::class,
                ReportWidget\ConversionRate::class,
                ReportWidget\MostSoldProduct::class,
                ReportWidget\LeastSoldProduct::class,
                ReportWidget\TotalOrder::class,
                ReportWidget\AverageOrderValue::class,
                ReportWidget\MostFavoriteProduct::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()->label(fn () => trans('Shop Configuration')),
                NavigationGroup::make()->label(fn () => trans('Customer Management')),
                NavigationGroup::make()->label(fn () => trans('CMS')),
                NavigationGroup::make()->label(fn () => trans('eCommerce')),
                NavigationGroup::make()->label(fn () => trans('Access')),
                NavigationGroup::make()->label(fn () => trans('System')),
            ])
            ->plugins([
                FilamentPasswordConfirmationPlugin::make()->routeMiddleware(['tenant']),
                \Hasnayeen\Themes\ThemesPlugin::make(),
            ])
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->unsavedChangesAlerts(fn () => ! $this->app->isLocal())
            // ->spa()
            ->maxContentWidth(MaxWidth::Full)
            ->databaseTransactions()
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
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class,
            ])
            ->middleware(
                [
                    //                    'universal', // PreventAccessFromCentralDomains does not work properly
                    'tenant',
                ],
                isPersistent: true
            )
            ->authMiddleware([
                Authenticate::class,
                'active:filament.tenant.account-deactivated.notice',
            ])
            ->routes(function () {

                Route::get('two-factor', TwoFactorAuthentication::class)
                    ->middleware('guest:admin')
                    ->name('two-factor');

                Route::middleware(Authenticate::class)
                    ->group(function () {
                        Route::get('account-deactivated', AccountDeactivatedNotice::class)
                            ->name('account-deactivated.notice');
                    });
            });
    }
}
