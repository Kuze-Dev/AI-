<?php

declare(strict_types=1);

namespace App\FilamentTenant\Clusters\Settings\Pages;

use App\Features\Shopconfiguration\PaymentGateway\PaypalGateway;
use App\Features\Shopconfiguration\PaymentGateway\StripeGateway;
use App\FilamentTenant\Support\Concerns\AuthorizeEcommerceSettings;
use App\Settings\PaymentSettings as SettingsPaymentSettings;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Forms;
use Illuminate\Auth\Middleware\RequirePassword;

class PaymentSettings extends TenantBaseSettings
{
    use AuthorizeEcommerceSettings;

    protected static string $settings = SettingsPaymentSettings::class;

    protected static ?string $navigationIcon = 'heroicon-s-credit-card';

    protected static ?string $title = 'Payment Settings';

    protected static string|array $routeMiddleware = RequirePassword::class.':filament.tenant.password.confirm';

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make([
                Forms\Components\Section::make(trans('PayPaL'))
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('paypal_secret_id'),
                        Forms\Components\TextInput::make('paypal_secret_key'),
                        Forms\Components\Toggle::make('paypal_production_mode')
                            ->inline(false)
                            ->label(fn ($state) => $state ? 'PayPal (Live)' : 'PayPal (sandbox)')
                            ->helperText('If the feature is activated, it is necessary to provide production keys. However, if the feature is deactivated, payment processing will occur in sandbox mode')
                            ->reactive(),
                    ])
                    ->hidden(fn () => ! TenantFeatureSupport::active(PaypalGateway::class)),
                Forms\Components\Section::make(trans('Stripe'))
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('stripe_publishable_key'),
                        Forms\Components\TextInput::make('stripe_secret_key'),
                        Forms\Components\Toggle::make('stripe_production_mode')
                            ->inline(false)
                            ->label(fn ($state) => $state ? 'Stripe (Live)' : 'Stripe (sandbox)')
                            ->helperText('If the feature is activated, it is necessary to provide production keys. However, if the feature is deactivated, payment processing will occur in sandbox mode')
                            ->reactive(),
                    ])
                    ->hidden(fn () => ! TenantFeatureSupport::active(StripeGateway::class)),

            ]),

        ];
    }
}
