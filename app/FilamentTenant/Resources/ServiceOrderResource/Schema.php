<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource;

use Domain\Address\Models\Address;
use Domain\ServiceOrder\Enums\ServiceOrderAddressType;
use Filament\Forms;
use Filament\Forms\Get;

final class Schema
{
    private function __construct()
    {
    }

    public static function address(string $field, ServiceOrderAddressType $type): array
    {
        return [
            Forms\Components\Placeholder::make('country_placeholder')
                ->label(trans('Country'))
                ->content(
                    fn (Get $get) => self::addressModel($get($field))->state->country->name
                ),

            Forms\Components\Placeholder::make('state_placeholder')
                ->label(trans('State'))
                ->content(
                    fn (Get $get) => self::addressModel($get($field))->state->name
                ),

            Forms\Components\Placeholder::make('city_province_placeholder')
                ->label(trans('City/Province'))
                ->content(
                    fn (Get $get) => self::addressModel($get($field))->city
                ),

            Forms\Components\Placeholder::make('zip_placeholder')
                ->label(trans('Zip'))
                ->content(
                    fn (Get $get) => self::addressModel($get($field))->zip_code
                ),

            Forms\Components\Hidden::make('type')
                ->default($type),

            Forms\Components\Hidden::make('country')
                ->dehydrateStateUsing(fn (Get $get) => self::addressModel($get($field))->state->country->name),

            Forms\Components\Hidden::make('state')
                ->dehydrateStateUsing(fn (Get $get) => self::addressModel($get($field))->state->name),

            Forms\Components\Hidden::make('label_as')
                ->dehydrateStateUsing(fn (Get $get) => self::addressModel($get($field))->label_as),

            Forms\Components\Hidden::make('address_line_1')
                ->dehydrateStateUsing(fn (Get $get) => self::addressModel($get($field))->address_line_1),

            Forms\Components\Hidden::make('zip_code')
                ->dehydrateStateUsing(fn (Get $get) => self::addressModel($get($field))->zip_code),

            Forms\Components\Hidden::make('city')
                ->dehydrateStateUsing(fn (Get $get) => self::addressModel($get($field))->city),

        ];
    }

    private static function addressModel(int|string $key): Address
    {
        return once(fn () => Address::whereKey($key)->sole());
    }
}
