<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\MediaresourceResource\Pages;

use App\FilamentTenant\Resources\MediaresourceResource;
use Filament\Resources\Pages\ListRecords;

class ListMediaresources extends ListRecords
{
    protected static string $resource = MediaresourceResource::class;

    protected function getActions(): array
    {
        return [

        ];
    }
}
