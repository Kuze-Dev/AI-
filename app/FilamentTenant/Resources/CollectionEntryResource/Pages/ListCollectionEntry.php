<?php

namespace App\FilamentTenant\Resources\CollectionEntryResource\Pages;

use App\FilamentTenant\Resources\CollectionEntryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollectionEntry extends ListRecords
{
    protected static string $resource = CollectionEntryResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}