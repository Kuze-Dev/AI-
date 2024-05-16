<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\FilamentTenant\Support\Concerns\AuthorizeEcommerceSettings;
use App\Settings\CustomerSettings as SettingCustomer;
use Domain\Blueprint\Models\Blueprint;
use Filament\Forms;
use Filament\Forms\Components\Card;

class CustomerSettings extends TenantBaseSettings
{
    // use AuthorizeEcommerceSettings;

    protected static string $settings = SettingCustomer::class;

    protected static ?string $navigationIcon = 'heroicon-s-users';

    protected function getFormSchema(): array
    {
        return [
            Card::make([
                Forms\Components\Select::make('blueprint_id')
                    ->label(trans('Blueprint'))
                    ->required()
                    ->preload()
                    ->optionsFromModel(Blueprint::class, 'name'),
            ]),

        ];
    }
}
