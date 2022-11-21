<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Admin\Models\Admin;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
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
        \Domain\Tenant\Models\Tenant::class => \App\Policies\TenantPolicy::class,
        \Domain\Blueprint\Models\Blueprint::class => \App\Policies\BlueprintPolicy::class,
        \Domain\Page\Models\Page::class => \App\Policies\PagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->configureNotificationUrls();

        /** @see https://freek.dev/1325-when-to-use-gateafter-in-laravel */
        Gate::after(fn ($user) => $user instanceof Admin ? $user->hasRole(config('domain.role.super_admin')) : null);

        ViewLog::can(fn (Admin $admin) => $admin->hasRole(config('domain.role.super_admin')));
    }

    protected function configureNotificationUrls(): void
    {
        VerifyEmailNotification::createUrlUsing(function (mixed $notifiable) {
            if ($notifiable instanceof Admin) {
                return URL::temporarySignedRoute(
                    'filament.auth.verification.verify',
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
                    'filament.auth.password.reset',
                    [
                        'token' => $token,
                        'email' => $notifiable->getEmailForPasswordReset(),
                    ]
                );
            }
        });
    }
}
