<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\AddressResource\StateResource\Pages;

use App\FilamentTenant\Resources\AddressResource\StateResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListState extends ListRecords
{
    protected static string $resource = StateResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
