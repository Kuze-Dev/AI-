<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CurrencyResource\Pages;

use App\FilamentTenant\Resources\CurrencyResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCurrency extends ListRecords
{
    protected static string $resource = CurrencyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
