<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlockResource\Blocks;

use App\FilamentTenant\Resources\BlockResource;
use Domain\Page\Exports\BlockExporter;
use Domain\Page\Imports\BlockImporter;
use Exception;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListBlocks extends ListRecords
{
    protected static string $resource = BlockResource::class;

    /** @throws Exception */
    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                ExportAction::make()
                    ->label(trans('Export Content'))
                    ->exporter(BlockExporter::class)
                    ->withActivityLog(
                        event: 'exported',
                        description: fn (ExportAction $action) => 'Exported '.$action->getModelLabel(),
                    ),
                ImportAction::make()
                    ->label(trans('Import Content'))
                    ->importer(BlockImporter::class)
                    ->withActivityLog(
                        event: 'imported',
                        description: fn (ImportAction $action) => 'Imported '.$action->getModelLabel(),
                    ),
            ])->hidden(fn () => ! filament_admin()->hasRole(config()->string('domain.role.super_admin'))),
        ];
    }

    #[\Override]
    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [12, 24, 48, -1];
    }
}
