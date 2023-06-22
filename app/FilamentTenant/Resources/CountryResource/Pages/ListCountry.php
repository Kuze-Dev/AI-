<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CountryResource\Pages;

use App\FilamentTenant\Resources\CountryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;


class ListCountry extends ListRecords
{
    protected static string $resource = CountryResource::class;

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
