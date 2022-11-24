<?php

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Exception;

class ListCollection extends ListRecords
{
    protected static string $resource = CollectionResource::class;

    /**
     * @return array
     */
    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
