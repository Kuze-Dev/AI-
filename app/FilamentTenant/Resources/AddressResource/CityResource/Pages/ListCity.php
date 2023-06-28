<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\AddressResource\CityResource\Pages;

use App\FilamentTenant\Resources\AddressResource\CityResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCity extends ListRecords
{
    protected static string $resource = CityResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}
