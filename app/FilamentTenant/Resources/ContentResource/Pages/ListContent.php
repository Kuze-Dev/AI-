<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ContentResource\Pages;

use App\FilamentTenant\Resources\ContentResource;
use Closure;
use Domain\Content\Exports\ContentExporter;
use Domain\Content\Models\Content;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListContent extends ListRecords
{
    protected static string $resource = ContentResource::class;

    #[\Override]
    protected function getTableRecordUrlUsing(): ?Closure
    {
        if (self::$resource::canViewAny()) {
            return fn (Content $record) => ContentResource::getUrl('entries.index', [$record]);
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
            Actions\ActionGroup::make([
                ExportAction::make()
                    ->label(trans('Export Content'))
                    ->exporter(ContentExporter::class)
                    ->withActivityLog(
                        event: 'exported',
                        description: fn (ExportAction $action) => 'Exported '.$action->getModelLabel(),
                    ),
            ]),
            // ->button()
            // ->color('gray')
            // ->icon('')
            // ->label(trans('More Actions')),
            // ->icon('heroicon-o-cog'),

        ];
    }
}
