<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\SliceResource\Slices;

use App\FilamentTenant\Resources\SliceResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Exception;

class ListSlices extends ListRecords
{
    protected static string $resource = SliceResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
