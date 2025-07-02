<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxonomyResource\Pages;

use App\FilamentTenant\Resources\TaxonomyResource;
use Domain\Taxonomy\Exports\TaxonomiesExporter;
use Domain\Taxonomy\Imports\TaxonomiesImporter;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListTaxonomies extends ListRecords
{
    protected static string $resource = TaxonomyResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                ExportAction::make()
                    ->label(trans('Export Taxonomies'))
                    ->exporter(TaxonomiesExporter::class)
                    ->chunkSize(500)
                    ->withActivityLog(
                        event: 'exported',
                        description: fn (ExportAction $action) => 'Exported '.$action->getModelLabel(),
                    ),
                ImportAction::make()
                    ->label(trans('Import Taxonomies'))
                    ->importer(TaxonomiesImporter::class)
                    ->withActivityLog(
                        event: 'imported',
                        description: fn (ImportAction $action) => 'Imported '.$action->getModelLabel(),
                    ),
            ])->hidden(fn () => ! filament_admin()->hasRole(config()->string('domain.role.super_admin'))),
        ];
    }
}
