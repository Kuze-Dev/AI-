<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\FormResource\Pages;

use App\FilamentTenant\Resources\FormResource;
use Domain\Form\Exports\FormExporter;
use Domain\Form\Imports\FormImporter;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListForms extends ListRecords
{
    protected static string $resource = FormResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                ExportAction::make()
                    ->label(trans('Export Forms'))
                    ->exporter(FormExporter::class)
                    ->withActivityLog(
                        event: 'exported',
                        description: fn (ExportAction $action) => 'Exported '.$action->getModelLabel(),
                    ),
                ImportAction::make()
                    ->label(trans('Import Forms'))
                    ->importer(FormImporter::class)
                    ->withActivityLog(
                        event: 'imported',
                        description: fn (ImportAction $action) => 'Imported '.$action->getModelLabel(),
                    ),
            ]),
        ];
    }
}
