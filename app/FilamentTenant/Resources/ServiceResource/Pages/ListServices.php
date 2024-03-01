<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceResource\Pages;

use App\FilamentTenant\Resources\ServiceResource;
use Exception;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    /** @throws Exception */
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
