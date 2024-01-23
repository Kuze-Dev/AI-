<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxZoneResource\Pages;

use App\FilamentTenant\Resources\TaxZoneResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTaxZones extends ListRecords
{
    protected static string $resource = TaxZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
