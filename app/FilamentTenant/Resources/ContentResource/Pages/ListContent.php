<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ContentResource\Pages;

use App\FilamentTenant\Resources\ContentEntryResource;
use App\FilamentTenant\Resources\ContentResource;
use Closure;
use Domain\Content\Models\Content;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContent extends ListRecords
{
    protected static string $resource = ContentResource::class;

    #[\Override]
    protected function getTableRecordUrlUsing(): ?Closure
    {
        if (self::$resource::canViewAny()) {
            return fn (Content $record) => ContentEntryResource::getUrl('index', [$record]);
        }

        return parent::getTableRecordUrlUsing();
    }

    /**
     * Declare action buttons that
     * are available on the page.
     */
    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
