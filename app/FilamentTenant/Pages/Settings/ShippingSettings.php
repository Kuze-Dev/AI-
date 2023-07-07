<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Settings\ShippingSettings as SettingsShippingSettings;
use Filament\Forms;

class ShippingSettings extends TenantBaseSettings
{
    protected static string $settings = SettingsShippingSettings::class;

    protected static ?string $navigationIcon = 'heroicon-s-credit-card';

    protected static ?string $title = 'Shipping Settings';

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Card::make([
                Forms\Components\Section::make(trans('Usps Shipping'))
                    ->collapsible()
                    ->schema([
                        Forms\Components\KeyValue::make('usps_credentials')
                            ->label('Usps Credentials')
                            ->disableAddingRows()
                            ->disableEditingKeys()
                            ->disableDeletingRows()
                            ->formatStateUsing(function ($state) {
                                if ($state != null) {
                                    return $state;
                                }

                                return [
                                    'username' => '',
                                    'password' => '',
                                ];
                            }),
                        Forms\Components\Toggle::make('usps_mode')
                            ->inline(false)
                            ->label(fn ($state) => $state ? 'Usps (Live)' : 'Usps (sandbox)')
                            ->helperText('If the feature is activated, it is necessary to provide production keys. However, if the feature is deactivated, payment processing will occur in sandbox mode')
                            ->reactive(),
                    ]),
            

            ]),

        ];
    }
}
