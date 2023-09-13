<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\FilamentTenant\Support\Concerns\AuthorizeEcommerceSettings;
use App\Settings\PaymentSettings as SettingsPaymentSettings;
use Filament\Forms;

class PaymentSettings extends TenantBaseSettings
{
    use AuthorizeEcommerceSettings;

    protected static string $settings = SettingsPaymentSettings::class;

    protected static ?string $navigationIcon = 'heroicon-s-credit-card';

    protected static ?string $title = 'Payment Settings';

    protected static string|array $middlewares = ['password.confirm:filament-tenant.auth.password.confirm'];

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Card::make([
                Forms\Components\Section::make(trans('PayPaL'))
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('paypal_secret_id'),
                        Forms\Components\TextInput::make('paypal_secret_key'),
                        Forms\Components\Toggle::make('paypal_mode')
                            ->inline(false)
                            ->label(fn ($state) => $state ? 'PayPal (Live)' : 'PayPal (sandbox)')
                            ->helperText('If the feature is activated, it is necessary to provide production keys. However, if the feature is deactivated, payment processing will occur in sandbox mode')
                            ->reactive(),
                    ])
                    ->hidden(fn () => !tenancy()->tenant?->features()->active(\App\Features\ECommerce\PaypalGateway::class)),
                Forms\Components\Section::make(trans('Stripe'))
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('stripe_publishable_key'),
                        Forms\Components\TextInput::make('stripe_secret_key'),
                        Forms\Components\Toggle::make('stripe_mode')
                            ->inline(false)
                            ->label(fn ($state) => $state ? 'Stripe (Live)' : 'Stripe (sandbox)')
                            ->helperText('If the feature is activated, it is necessary to provide production keys. However, if the feature is deactivated, payment processing will occur in sandbox mode')
                            ->reactive(),
                    ])
                    ->hidden(fn () => !tenancy()->tenant?->features()->active(\App\Features\ECommerce\StripeGateway::class)),

            ]),

        ];
    }
}
