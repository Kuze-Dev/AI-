<?php

namespace App\FilamentTenant\Resources\MediaresourceResource\Pages;

use App\FilamentTenant\Resources\MediaresourceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMediaresources extends ListRecords
{
    protected static string $resource = MediaresourceResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
