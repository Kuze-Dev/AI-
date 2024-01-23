<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\MenuResource\Pages;

use App\FilamentTenant\Resources\MenuResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
