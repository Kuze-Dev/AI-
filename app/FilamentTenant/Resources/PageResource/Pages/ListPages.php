<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\FilamentTenant\Resources\PageResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Exception;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
