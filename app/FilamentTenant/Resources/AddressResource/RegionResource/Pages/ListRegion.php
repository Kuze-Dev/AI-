<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\AddressResource\RegionResource\Pages;

use App\FilamentTenant\Resources\AddressResource\RegionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRegion extends ListRecords
{
    protected static string $resource = RegionResource::class;

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
