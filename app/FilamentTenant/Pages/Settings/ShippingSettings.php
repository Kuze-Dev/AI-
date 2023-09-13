<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\FilamentTenant\Support\Concerns\AuthorizeEcommerceSettings;
use App\Settings\ShippingSettings as SettingsShippingSettings;
use Filament\Forms;

class ShippingSettings extends TenantBaseSettings
{
    use AuthorizeEcommerceSettings;

    protected static string $settings = SettingsShippingSettings::class;

    protected static ?string $navigationIcon = 'heroicon-s-truck';

    protected static ?string $title = 'Shipping Settings';

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Card::make([
                Forms\Components\Section::make(trans('Usps Shipping'))
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('usps_username')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('usps_password')
                            ->translateLabel(),
                        Forms\Components\Toggle::make('usps_production_mode')
                            ->inline(false)
                            ->label(fn ($state) => trans('Usps (:value)', ['value' => $state ? 'Live' : 'Sandbox']))
                            ->helperText(
                                trans(
                                    'If the feature is activated, it is necessary to provide production keys. ' .
                                        'However, if the feature is deactivated, payment processing will occur in sandbox mode'
                                )
                            )
                            ->reactive(),
                    ])->hidden(
                        fn () => ! tenancy()->tenant?->features()->active(\App\Features\ECommerce\ShippingUsps::class)
                    ),
                Forms\Components\Section::make(trans('Ups Shipping'))
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('ups_shipper_account')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('ups_client_id')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('ups_client_secret')
                            ->translateLabel(),
                        Forms\Components\Toggle::make('ups_production_mode')
                            ->inline(false)
                            ->label(fn ($state) => trans('Usps (:value)', ['value' => $state ? 'Live' : 'Sandbox']))
                            ->helperText(
                                trans(
                                    'If the feature is activated, it is necessary to provide production keys. ' .
                                        'However, if the feature is deactivated, payment processing will occur in sandbox mode'
                                )
                            )
                            ->reactive(),
                    ])->hidden(
                        fn () => ! tenancy()->tenant?->features()->active(\App\Features\ECommerce\ShippingUps::class)
                    ),

            ]),

        ];
    }
}
