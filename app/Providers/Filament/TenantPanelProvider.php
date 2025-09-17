<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Admin\Themes\Mint;
use App\FilamentTenant\Livewire\Auth\TwoFactorAuthentication;
use App\FilamentTenant\Pages\AccountDeactivatedNotice;
use App\FilamentTenant\Pages\TenantFullAIWidget;
use App\FilamentTenant\Widgets\DeployStaticSite;
use App\FilamentTenant\Widgets\Report as ReportWidget;
use App\Settings\SiteSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
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
use Stancl\Tenancy\Middleware\ScopeSessions;

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
            ->profile()
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn () => filament_admin()->full_name),
                'Widget' => MenuItem::make()
                    ->label('Access Widget')
                    ->icon('heroicon-o-command-line')
                    ->url('/admin/ai-widget')
                    ->openUrlInNewTab(),

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
                Dashboard::class,
                TenantFullAIWidget::class,
            ])
            ->widgets([
                AccountWidget::class,
                DeployStaticSite::class,
                ReportWidget\TotalSales::class,
                ReportWidget\ConversionRate::class,
                ReportWidget\MostSoldProduct::class,
                ReportWidget\LeastSoldProduct::class,
                ReportWidget\TotalOrder::class,
                ReportWidget\AverageOrderValue::class,
                ReportWidget\MostFavoriteProduct::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()->label(fn () => trans('CMS')),
                NavigationGroup::make()->label(fn () => trans('eCommerce')),
                NavigationGroup::make()->label(fn () => trans('Access')),
                NavigationGroup::make()->label(fn () => trans('Shop Configuration')),
                NavigationGroup::make()->label(fn () => trans('Service Management')),
                NavigationGroup::make()->label(fn () => trans('Customer Management')),
                NavigationGroup::make()->label(fn () => trans('System')),

            ])

            ->plugins([
                FilamentPasswordConfirmationPlugin::make()->routeMiddleware(['tenant']),
                \Hasnayeen\Themes\ThemesPlugin::make()
                    ->registerTheme(
                        [
                            // 'default' => \Hasnayeen\Themes\Themes\DefaultTheme::class,
                            // 'sunset' => \Hasnayeen\Themes\Themes\Sunset::class,
                            // 'nord' => \Hasnayeen\Themes\Themes\Nord::class,
                            'mint' => Mint::class,

                        ],
                        // override: true,
                    ),
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
                ScopeSessions::class,
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

                Route::middleware(['tenant', 'auth:admin'])
                    ->prefix('admin')
                    ->group(function () {
                        Route::get('ai-widget', \App\FilamentTenant\Pages\TenantFullAIWidget::class)
                            ->name('tenant.ai-widget');
                    });

            });

    }
}
