<?php

declare(strict_types=1);

namespace App\FilamentTenant\Clusters\Settings\Pages;

use App\FilamentTenant\Support\Concerns\AuthorizeEcommerceSettings;
use App\Settings\RewardPointsSettings as SettingsRewardPointsSettings;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;

class RewardPointsSettings extends TenantBaseSettings
{
    use AuthorizeEcommerceSettings;

    protected static string $settings = SettingsRewardPointsSettings::class;

    protected static ?string $navigationIcon = 'heroicon-s-tag';

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                TextInput::make('minimum_amount')
                    ->label(trans('Minimum amount to spend'))
                    ->minValue(1)
                    ->numeric()
                    ->required(),

                TextInput::make('equivalent_point')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
            ])->columns(2),

        ];
    }
}
