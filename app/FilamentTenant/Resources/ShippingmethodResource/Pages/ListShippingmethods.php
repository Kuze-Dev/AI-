<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ShippingmethodResource\Pages;

use App\FilamentTenant\Resources\ShippingmethodResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShippingmethods extends ListRecords
{
    protected static string $resource = ShippingmethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
