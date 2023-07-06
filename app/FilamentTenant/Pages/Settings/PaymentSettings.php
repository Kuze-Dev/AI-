<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Settings\PaymentSettings as SettingsPaymentSettings;
use Filament\Forms;

class PaymentSettings extends TenantBaseSettings
{
    protected static string $settings = SettingsPaymentSettings::class;

    protected static ?string $navigationIcon = 'heroicon-s-credit-card';

    protected static ?string $title = 'Payment Settings';

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Card::make([
                Forms\Components\Section::make(trans('PayPaL'))
                    ->collapsible()
                    ->schema([
                        Forms\Components\KeyValue::make('paypal_credentials')
                            ->label('Paypal Credentials')
                            ->disableAddingRows()
                            ->disableEditingKeys()
                            ->disableDeletingRows()
                            ->formatStateUsing(function ($state) {
                                if ($state != null) {
                                    return $state;
                                }

                                return [
                                    'paypal_secret_id' => '',
                                    'paypal_secret_key' => '',
                                ];
                            }),
                        Forms\Components\Toggle::make('paypal_mode')
                            ->inline(false)
                            ->label(fn ($state) => $state ? 'PayPal (Live)' : 'PayPal (sandbox)')
                            ->helperText('If the feature is activated, it is necessary to provide production keys. However, if the feature is deactivated, payment processing will occur in sandbox mode')
                            ->reactive(),
                    ]),
                Forms\Components\Section::make(trans('Stripe'))
                    ->schema([
                        Forms\Components\KeyValue::make('stripe_credentials')
                            ->label('Stripe Credentials')
                            ->disableAddingRows()
                            ->disableEditingKeys()
                            ->disableDeletingRows()
                            ->formatStateUsing(function ($record) {
                                if ($record) {
                                    return $record->credentials;
                                }

                                return [
                                    'stripe_publishable_key' => '',
                                    'stripe_secret_key' => '',
                                ];
                            }),
                    ]),
                Forms\Components\Toggle::make('stripe_mode')
                    ->inline(false)
                    ->label(fn ($state) => $state ? 'Stripe (Live)' : 'Stripe (sandbox)')
                    ->helperText('If the feature is activated, it is necessary to provide production keys. However, if the feature is deactivated, payment processing will occur in sandbox mode')
                    ->reactive(),

            ]),

        ];
    }
}
