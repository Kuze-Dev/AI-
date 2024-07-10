<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\FilamentTenant\Support\Concerns\AuthorizeEcommerceSettings;
use App\Settings\CustomerSettings as SettingCustomer;
use Domain\Blueprint\Models\Blueprint;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Auth;

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
                    ->reactive()
                    ->optionsFromModel(Blueprint::class, 'name')
                    ->disabled(fn () => (app(SettingCustomer::class)->blueprint_id && Auth::user()?->id !== 1) ? true : false),
            ]),
            Forms\Components\Section::make(trans('Customer Import Export Settings'))
                ->schema([
                    Forms\Components\TextInput::make('date_format')
                        ->label(trans('Date Format'))
                        ->required()
                        ->helpertext('date format for validation and export and import customer the default value is default ex: format m-d-Y')
                        ->default('default'),
                ]),

        ];
    }
}
