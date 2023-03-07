<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\GlobalsResource\Pages;

use App\FilamentTenant\Resources\GlobalsResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGlobals extends ListRecords
{
    protected static string $resource = GlobalsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
