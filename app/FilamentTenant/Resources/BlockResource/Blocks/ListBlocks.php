<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlockResource\Blocks;

use App\FilamentTenant\Resources\BlockResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Exception;

class ListBlocks extends ListRecords
{
    protected static string $resource = BlockResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
