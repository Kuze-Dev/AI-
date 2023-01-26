<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlueprintResource\Pages;

use App\FilamentTenant\Resources\BlueprintResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBlueprints extends ListRecords
{
    protected static string $resource = BlueprintResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
