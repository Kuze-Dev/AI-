<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionEntryResource;
use App\FilamentTenant\Resources\CollectionResource;
use Closure;
use Domain\Collection\Models\Collection;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollection extends ListRecords
{
    protected static string $resource = CollectionResource::class;

    protected function getTableRecordUrlUsing(): ?Closure
    {
        return fn (Collection $record) => CollectionEntryResource::getUrl('index', [$record]);
    }

    /**
     * Declare action buttons that
     * are available on the page.
     */
    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
