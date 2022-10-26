<?php

declare(strict_types=1);

namespace App\Providers;

use App\FilamentTenant\Livewire\Auth;
use App\FilamentTenant\Middleware\Authenticate;
use Artificertech\FilamentMultiContext\ContextServiceProvider;
use Artificertech\FilamentMultiContext\Http\Middleware\ApplyContext;
use Filament\Facades\Filament;
use Filament\Navigation\UserMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

class FilamentTenantServiceProvider extends ContextServiceProvider
{
    /** @var array<string, class-string> */
    protected array $livewireComponents;

    public static string $name = 'filament-tenant';

    public function packageBooted(): void
    {
        parent::packageBooted();

        $this->bootLivewireComponents();

        $this->registerRoutes();

        Filament::serving(function () {
            // TODO: Move to `getUserMenuItems()` after PR is merged https://github.com/artificertech/filament-multi-context/pull/27
            if (Filament::currentContext() !== static::$name) {
                return;
            }

            Filament::registerUserMenuItems([
                'logout' => UserMenuItem::make()->url(route('filament-tenant.auth.logout')),
            ]);
        });
    }

    // TODO: Remove after PR is merged https://github.com/artificertech/filament-multi-context/pull/26
    protected function bootLivewireComponents(): void
    {
        foreach (array_merge($this->livewireComponents) as $alias => $class) {
            Livewire::component($alias, $class);
        }
    }

    protected function registerRoutes(): void
    {
        Route::middleware(array_merge([ApplyContext::class . ':' . static::$name], $this->contextConfig('middleware.base')))
            ->domain($this->contextConfig('domain'))
            ->prefix('admin')
            ->name(static::$name . '.auth.')
            ->group(function () {
                Route::get('login', Auth\Login::class)
                    ->middleware('guest:admin')
                    ->name('login');
                Route::get('two-factor', Auth\TwoFactorAuthentication::class)
                    ->middleware('guest:admin')
                    ->name('two-factor');

                Route::prefix('password')
                    ->name('password.')
                    ->group(function () {
                        Route::get('reset', Auth\RequestPasswordReset::class)
                            ->middleware('guest:admin')
                            ->name('request');
                        Route::get('reset/{token}', Auth\ResetPassword::class)
                            ->middleware('guest:admin')
                            ->name('reset');
                        Route::get('confirm', Auth\ConfirmPassword::class)
                            ->middleware(Authenticate::class)
                            ->name('confirm');
                    });

                Route::middleware(Authenticate::class)
                    ->group(function () {
                        Route::get('account-deactivated', Auth\AccountDeactivatedNotice::class)
                            ->name('account-deactivated.notice');

                        Route::prefix('verify')
                            ->name('verification.')
                            ->group(function () {
                                Route::get('/', Auth\EmailVerificationNotice::class)
                                    ->name('notice');
                                Route::get('/{id}/{hash}', Auth\VerifyEmail::class)
                                    ->name('verify');
                            });

                        Route::post('logout', function (Request $request) {
                            Filament::auth()->logout();

                            $request->session()->invalidate();
                            $request->session()->regenerateToken();

                            return redirect()->route(static::$name . '.auth.login');
                        })->name('logout');
                    });
            });
    }
}
