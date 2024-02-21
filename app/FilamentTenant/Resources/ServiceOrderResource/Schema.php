<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource;

use Domain\Address\Models\Address;
use Filament\Forms;
use Filament\Forms\Get;

final class Schema
{
    private function __construct()
    {
    }

    public static function address(string $field): array
    {
        return [
            Forms\Components\Placeholder::make('country')
                ->translateLabel()
                ->content(
                    fn (Get $get) => self::addressModel($get($field))->state->country->name
                ),

            Forms\Components\Placeholder::make('state')
                ->translateLabel()
                ->content(
                    fn (Get $get) => self::addressModel($get($field))->state->name
                ),

            Forms\Components\Placeholder::make(trans('City/Province'))
                ->content(
                    fn (Get $get) => self::addressModel($get($field))->city
                ),

            Forms\Components\Placeholder::make('zip')
                ->translateLabel()
                ->content(
                    fn (Get $get) => self::addressModel($get($field))->zip_code
                ),
        ];
    }

    private static function addressModel(int|string $key): Address
    {
        return once(fn () => Address::whereKey($key)->sole());
    }
}
