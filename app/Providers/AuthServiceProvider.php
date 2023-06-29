<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Admin\Models\Admin;
use Domain\Customer\Models\Customer;
use Domain\Tenant\Models\Tenant;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

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
        \Spatie\Activitylog\Models\Activity::class => \App\Policies\ActivityPolicy::class,
        \Domain\Tenant\Models\Tenant::class => \App\Policies\TenantPolicy::class,
        \Domain\Blueprint\Models\Blueprint::class => \App\Policies\BlueprintPolicy::class,
        \Domain\Menu\Models\Menu::class => \App\Policies\MenuPolicy::class,
        \Domain\Page\Models\Page::class => \App\Policies\PagePolicy::class,
        \Domain\Page\Models\Block::class => \App\Policies\BlockPolicy::class,
        \Domain\Form\Models\Form::class => \App\Policies\FormPolicy::class,
        \Domain\Form\Models\FormSubmission::class => \App\Policies\FormSubmissionPolicy::class,
        \Domain\Taxonomy\Models\Taxonomy::class => \App\Policies\TaxonomyPolicy::class,
        \Domain\Content\Models\Content::class => \App\Policies\ContentPolicy::class,
        \Domain\Content\Models\ContentEntry::class => \App\Policies\ContentEntryPolicy::class,
        \Domain\Globals\Models\Globals::class => \App\Policies\GlobalsPolicy::class,
    ];

    /** Register any authentication / authorization services. */
    public function boot(): void
    {
        $this->configureNotificationUrls();

        /** @see https://freek.dev/1325-when-to-use-gateafter-in-laravel */
        Gate::after(fn ($user) => $user instanceof Admin ? $user->hasRole(config('domain.role.super_admin')) : null);
    }

    protected function configureNotificationUrls(): void
    {
        VerifyEmailNotification::createUrlUsing(function (mixed $notifiable) {
            if ($notifiable instanceof Customer) {
                /** @var Tenant $tenant */
                $tenant = tenancy()->tenant;

                $hostName = (app()->environment('local') ? 'http://' : 'https://') . $tenant->domains->first()?->domain;

                return $hostName . URL::temporarySignedRoute(
                    'tenant.api.customer.verify',
                    now()->addMinutes(Config::get('auth.verification.expire', 60)),
                    [
                        'id' => $notifiable->getKey(),
                        'hash' => sha1($notifiable->getEmailForVerification()),
                    ],
                    false
                );
            }

            if ($notifiable instanceof Admin) {
                if (tenancy()->initialized) {
                    /** @var Tenant $tenant */
                    $tenant = tenancy()->tenant;

                    $hostName = (app()->environment('local') ? 'http://' : 'https://') . $tenant->domains->first()?->domain;
                    $routeName = 'filament-tenant.auth.verification.verify';
                } else {
                    $hostName = url('/', secure: app()->environment('local'));
                    $routeName = 'filament.auth.verification.verify';
                }

                return $hostName . URL::temporarySignedRoute(
                    $routeName,
                    now()->addMinutes(Config::get('auth.verification.expire', 60)),
                    [
                        'id' => $notifiable->getKey(),
                        'hash' => sha1($notifiable->getEmailForVerification()),
                    ],
                    false
                );
            }
        });

        ResetPasswordNotification::createUrlUsing(function (mixed $notifiable, string $token) {

            if ($notifiable instanceof Customer) {

                /** @var Tenant $tenant */
                $tenant = tenancy()->tenant;

                if ($url = config('domain.customer.password_reset_url')) {
                    return $url.'?'.http_build_query([
                        'token' => $token,
                        'email' => $notifiable->getEmailForPasswordReset(),
                    ]);
                }

                $hostName = (app()->environment('local') ? 'http://' : 'https://') . $tenant->domains->first()?->domain;
                $routeName = 'filament-tenant.auth.password.reset';

                return $hostName . URL::route(
                    $routeName,
                    [
                        'token' => $token,
                        'email' => $notifiable->getEmailForPasswordReset(),
                    ],
                    false
                );
            }

            if ($notifiable instanceof Admin) {
                if (tenancy()->initialized) {
                    /** @var Tenant $tenant */
                    $tenant = tenancy()->tenant;

                    $hostName = (app()->environment('local') ? 'http://' : 'https://') . $tenant->domains->first()?->domain;
                    $routeName = 'filament-tenant.auth.password.reset';
                } else {
                    $hostName = url('/', secure: app()->environment('local'));
                    $routeName = 'filament.auth.password.reset';
                }

                return $hostName . URL::route(
                    $routeName,
                    [
                        'token' => $token,
                        'email' => $notifiable->getEmailForPasswordReset(),
                    ],
                    false
                );
            }
        });
    }
}
