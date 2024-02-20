<?php

declare(strict_types=1);

namespace App\Providers;

use App\Settings\ECommerceSettings;
use App\Settings\FormSettings;
use App\Settings\SiteSettings;
use Domain\Admin\Models\Admin;
use Domain\Auth\Contracts\HasEmailVerificationOTP;
use Domain\Customer\Models\Customer;
use Domain\Tenant\TenantSupport;
use Filament\Facades\Filament;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
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
        \Domain\Address\Models\Country::class => \App\Policies\CountryPolicy::class,
        \Domain\Currency\Models\Currency::class => \App\Policies\CurrencyPolicy::class,
        \Domain\Product\Models\Product::class => \App\Policies\ProductPolicy::class,
        \Domain\Address\Models\Address::class => \App\Policies\AddressPolicy::class,
        \Domain\Cart\Models\Cart::class => \App\Policies\CartPolicy::class,
        \Domain\Cart\Models\CartLine::class => \App\Policies\CartLinePolicy::class,
        \Domain\ShippingMethod\Models\ShippingMethod::class => \App\Policies\ShippingMethodPolicy::class,
        \Domain\PaymentMethod\Models\PaymentMethod::class => \App\Policies\PaymentMethodPolicy::class,
        \Domain\Customer\Models\Customer::class => \App\Policies\CustomerPolicy::class,
        \Domain\Tier\Models\Tier::class => \App\Policies\TierPolicy::class,
        \Domain\Order\Models\Order::class => \App\Policies\OrderPolicy::class,
        \Domain\Discount\Models\Discount::class => \App\Policies\DiscountPolicy::class,
        \Domain\Taxation\Models\TaxZone::class => \App\Policies\TaxZonePolicy::class,
        \Domain\Internationalization\Models\Locale::class => \App\Policies\LocalePolicy::class,
        \Domain\Site\Models\Site::class => \App\Policies\SitePolicy::class,
        \Domain\Service\Models\Service::class => \App\Policies\ServicePolicy::class,
        \Domain\ServiceOrder\Models\ServiceOrder::class => \App\Policies\ServiceOrderPolicy::class,
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
        VerifyEmailNotification::toMailUsing(function (mixed $notifiable, $url): MailMessage {

            if ($notifiable instanceof HasEmailVerificationOTP && $notifiable->isEmailVerificationUseOTP()) {
                return (new MailMessage())
                    ->from(
                        TenantSupport::initialized() ?
                        (app(FormSettings::class)->sender_email ? config('mail.from.address') : config('mail.from.address')) :
                        config('mail.from.address')
                    )
                    ->subject(trans('Verify Email Address'))
                    ->line(trans('Please copy OTP below to verify your email address.'))
                    ->line('OTP: '.$notifiable->generateEmailVerificationOTP())
                    ->line(trans('If you did not create an account, no further action is required.'));
            }

            // copied from \Illuminate\Auth\Notifications\VerifyEmail::buildMailMessage($url)
            // https://github.com/laravel/framework/blob/v10.16.1/src/Illuminate/Auth/Notifications/VerifyEmail.php#L62
            return (new MailMessage())
                ->from(
                    TenantSupport::initialized() ?
                    (app(FormSettings::class)->sender_email ? config('mail.from.address') : config('mail.from.address')) :
                    config('mail.from.address')
                )
                ->subject(trans('Verify Email Address'))
                ->line(trans('Please click the button below to verify your email address.'))
                ->action(trans('Verify Email Address'), $url)
                ->line(trans('If you did not create an account, no further action is required.'));
        });

        VerifyEmailNotification::createUrlUsing(function (mixed $notifiable) {

            if ($notifiable instanceof HasEmailVerificationOTP && $notifiable->isEmailVerificationUseOTP()) {
                return null;
            }

            if ($notifiable instanceof Customer) {

                $hostName = Request::getScheme().'://'.TenantSupport::model()->domains->first()?->domain;

                return $hostName.URL::temporarySignedRoute(
                    'tenant.api.customer.verification.verify',
                    now()->addMinutes(Config::get('auth.verification.expire', 60)),
                    [
                        'customer' => $notifiable->getRouteKey(),
                        'hash' => sha1($notifiable->getEmailForVerification()),
                    ],
                    false
                );
            }

            if ($notifiable instanceof Admin) {
                return Filament::getVerifyEmailUrl($notifiable);
            }
        });
        ResetPasswordNotification::createUrlUsing(function (mixed $notifiable, string $token) {

            if ($notifiable instanceof Customer) {
                $baseUrl = app(ECommerceSettings::class)->domainWithScheme()
                    ?? app(SiteSettings::class)->domainWithScheme();

                return $baseUrl.'/password/reset'.'?'.http_build_query([
                    'token' => $token,
                    'expired_at' => now()->addMinutes(config('auth.passwords.customer.expire'))->timestamp,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ]);
            }

            if ($notifiable instanceof Admin) {
                return Filament::getResetPasswordUrl($token, $notifiable);
            }
        });
    }
}
