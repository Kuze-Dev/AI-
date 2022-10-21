<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Livewire\Admin\Auth\AccountDeactivatedNotice;
use App\Http\Livewire\Admin\Auth\ConfirmPassword;
use App\Http\Livewire\Admin\Auth\EmailVerificationNotice;
use App\Http\Livewire\Admin\Auth\RequestPasswordReset;
use App\Http\Livewire\Admin\Auth\ResetPassword;
use App\Http\Livewire\Admin\Auth\TwoFactorAuthentication;
use App\Http\Livewire\Admin\Auth\VerifyEmail as VerifyEmailLivewire;
use Domain\Admin\Models\Admin;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Saade\FilamentLaravelLog\Pages\ViewLog;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \Domain\Admin\Models\Admin::class => \App\Policies\AdminPolicy::class,
        \Spatie\Permission\Models\Role::class => \App\Policies\RolePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->registerRoutes();
        $this->configureNotificationUrls();

        /** @see https://freek.dev/1325-when-to-use-gateafter-in-laravel */
        Gate::after(fn ($user) => $user instanceof Admin ? $user->hasRole(config('domain.role.super_admin')) : null);

        ViewLog::can(fn (Admin $admin) => $admin->hasRole(config('domain.role.super_admin')));
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
                                Route::get('/{id}/{hash}', VerifyEmailLivewire::class)
                                    ->name('verify');
                            });
                    });
            });
    }

    protected function configureNotificationUrls(): void
    {
        VerifyEmailNotification::createUrlUsing(function (mixed $notifiable) {
            if ($notifiable instanceof Admin) {
                return URL::temporarySignedRoute(
                    'admin.verification.verify',
                    now()->addMinutes(Config::get('auth.verification.expire', 60)),
                    [
                        'id' => $notifiable->getKey(),
                        'hash' => sha1($notifiable->getEmailForVerification()),
                    ]
                );
            }
        });

        ResetPasswordNotification::createUrlUsing(function (mixed $notifiable, string $token) {
            if ($notifiable instanceof Admin) {
                return URL::route(
                    'admin.password.reset',
                    [
                        'token' => $token,
                        'email' => $notifiable->getEmailForPasswordReset(),
                    ]
                );
            }
        });
    }
}
