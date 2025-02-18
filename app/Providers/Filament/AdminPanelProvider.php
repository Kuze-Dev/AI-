<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Livewire\Auth\TwoFactorAuthentication;
use App\Filament\Pages\AccountDeactivatedNotice;
use App\Filament\Pages\ConfirmPassword;
use App\Filament\Pages\EditProfile;
use App\Filament\Pages\Login;
use App\Settings\SiteSettings;
use Filament\Facades\Filament;
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
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;

class AdminPanelProvider extends PanelProvider
{
    #[\Override]
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->domains(config('tenancy.central_domains'))
            ->path('admin')
            ->authGuard('admin')
            ->authPasswordBroker('admin')
            ->login(Login::class)
            ->profile(EditProfile::class)
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
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()->label(fn () => trans('Access')),
                NavigationGroup::make()->label(fn () => trans('System')),
            ])
            ->plugin(FilamentSpatieLaravelHealthPlugin::make()->navigationGroup(trans('System')))
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
            ])
            ->middleware(
                [
                    'universal',
                ],
                isPersistent: true
            )
            ->authMiddleware([
                Authenticate::class,
                'active:filament.admin.account-deactivated.notice',
            ])
            ->routes(function () {

                Route::get('two-factor', TwoFactorAuthentication::class)
                    ->middleware('guest:admin')
                    ->name('two-factor');

                Route::get('password/confirm', ConfirmPassword::class)
                    ->middleware(Authenticate::class)
                    ->name('password.confirm');

                Route::middleware(Authenticate::class)
                    ->group(function () {
                        Route::get('account-deactivated', AccountDeactivatedNotice::class)
                            ->name('account-deactivated.notice');
                    });
            });
    }
}
