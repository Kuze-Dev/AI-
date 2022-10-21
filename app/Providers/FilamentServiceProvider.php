<?php

declare(strict_types=1);

namespace App\Providers;

use App\Filament\Livewire\Auth\AccountDeactivatedNotice;
use App\Filament\Livewire\Auth\ConfirmPassword;
use App\Filament\Livewire\Auth\EmailVerificationNotice;
use App\Filament\Livewire\Auth\RequestPasswordReset;
use App\Filament\Livewire\Auth\ResetPassword;
use App\Filament\Livewire\Auth\TwoFactorAuthentication;
use App\Filament\Livewire\Auth\VerifyEmail;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/** @property \Illuminate\Foundation\Application $app */
class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Filament::serving(function () {
            Filament::registerNavigationGroups([

                NavigationGroup::make('Access')
                    ->icon('heroicon-s-lock-closed'),

                NavigationGroup::make('Settings')
                    ->icon('heroicon-s-cog'),

                NavigationGroup::make('System')
                    ->icon('heroicon-s-exclamation'),
            ]);
        });

        Filament::registerRenderHook(
            'footer.start',
            fn () => <<<HTML
                    <p>
                        Powered by
                        <a
                            href="https://halcyonwebdesign.com.ph/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-gray-300 hover:text-primary-500 transition"
                        >
                            Halcyon Web Design
                        </a>
                    </p>

                HTML,
        );

        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        Route::middleware(config('filament.middleware.base'))
            ->prefix('admin')
            ->name('admin.')
            ->group(function () {
                Route::get('two-factor', TwoFactorAuthentication::class)
                    ->middleware('guest:admin')
                    ->name('two-factor');

                Route::prefix('password')
                    ->name('password.')
                    ->group(function () {
                        Route::get('reset', RequestPasswordReset::class)
                            ->middleware('guest:admin')
                            ->name('request');
                        Route::get('reset/{token}', ResetPassword::class)
                            ->middleware('guest:admin')
                            ->name('reset');
                        Route::get('confirm', ConfirmPassword::class)
                            ->middleware(\Filament\Http\Middleware\Authenticate::class)
                            ->name('confirm');
                    });

                Route::middleware(\Filament\Http\Middleware\Authenticate::class)
                    ->group(function () {
                        Route::get('account-deactivated', AccountDeactivatedNotice::class)
                            ->name('account-deactivated.notice');

                        Route::prefix('verify')
                            ->name('verification.')
                            ->group(function () {
                                Route::get('/', EmailVerificationNotice::class)
                                    ->name('notice');
                                Route::get('/{id}/{hash}', VerifyEmail::class)
                                    ->name('verify');
                            });
                    });
            });
    }
}
