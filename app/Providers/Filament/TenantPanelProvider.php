<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Pages\Dashboard;
use App\Settings\SiteSettings;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use App\Filament\Admin\Themes\Mint;
use Filament\Widgets\AccountWidget;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Route;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Stancl\Tenancy\Middleware\ScopeSessions;
use Illuminate\Session\Middleware\StartSession;
use App\FilamentTenant\Pages\TenantFullAIWidget;
use App\FilamentTenant\Widgets\DeployStaticSite;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\FilamentTenant\Pages\AccountDeactivatedNotice;
use App\FilamentTenant\Widgets\Report as ReportWidget;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use App\FilamentTenant\Livewire\Auth\TwoFactorAuthentication;
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
            ->profile()
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn () => filament_admin()->full_name),
                'widget' => MenuItem::make()
                    ->label('Access Widget')
                    ->icon('heroicon-o-command-line')
                    ->url('/admin/ai-widget')
                    ->openUrlInNewTab()
                    ->visible(fn (): bool => TenantFeatureSupport::active(\App\Features\AI\UploadBase::class)),
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

            });

    }
}
