<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Settings\ShippingSettings as SettingsShippingSettings;
use Filament\Forms;

class ShippingSettings extends TenantBaseSettings
{
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
                                    'If the feature is activated, it is necessary to provide production keys. '.
                                    'However, if the feature is deactivated, payment processing will occur in sandbox mode'
                                )
                            )
                            ->reactive(),
                    ]),

                Forms\Components\Section::make(trans('Ups Shipping'))
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('access_license_number')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('ups_username')
                            ->translateLabel(),
                        Forms\Components\TextInput::make('ups_password')
                            ->translateLabel(),
                        Forms\Components\Toggle::make('ups_production_mode')
                            ->inline(false)
                            ->label(fn ($state) => trans('Usps (:value)', ['value' => $state ? 'Live' : 'Sandbox']))
                            ->helperText(
                                trans(
                                    'If the feature is activated, it is necessary to provide production keys. '.
                                    'However, if the feature is deactivated, payment processing will occur in sandbox mode'
                                )
                            )
                            ->reactive(),
                    ]),

            ]),

        ];
    }
}
