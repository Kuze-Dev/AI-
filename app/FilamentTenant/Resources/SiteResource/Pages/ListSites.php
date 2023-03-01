<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\SiteResource\Pages;

use App\FilamentTenant\Resources\SiteResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSites extends ListRecords
{
    protected static string $resource = SiteResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
