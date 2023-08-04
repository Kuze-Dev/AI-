<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Features\ECommerce\RewardPoints;
use App\Settings\RewardPointsSettings as SettingsRewardPointsSettings;
use Closure;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;

class RewardPointsSettings extends TenantBaseSettings
{
    protected static string $settings = SettingsRewardPointsSettings::class;

    protected static ?string $navigationIcon = 'heroicon-s-tag';

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                TextInput::make('minimum_amount')
                    ->label(trans('Minimum amount to spend'))
                    ->minValue(1)
                    ->rules([
                        function () {
                            return function (string $attribute, mixed $value, Closure $fail) {
                                if (preg_match('/^0\d*$/', $value)) {
                                    $fail('The ' . $attribute . ' must not start with a number 0.');
                                }
                            };
                        },
                    ])
                    ->numeric()
                    ->required(),

                TextInput::make('equivalent_point')
                    ->rules([
                        function () {
                            return function (string $attribute, mixed $value, Closure $fail) {
                                if (preg_match('/^0\d*$/', $value)) {
                                    $fail('The ' . $attribute . ' must not start with a number 0.');
                                }
                            };
                        },
                    ])
                    ->numeric()
                    ->required(),
            ])->columns(2),

        ];
    }

    protected static function authorizeAccess(): bool
    {
        return parent::authorizeAccess() && tenancy()->tenant->features()->active(RewardPoints::class);
    }
}
