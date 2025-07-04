<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlueprintResource\Pages;

use App\FilamentTenant\Resources\BlueprintResource;
use Domain\Blueprint\Exports\BlueprintExporter;
use Domain\Blueprint\Imports\BlueprintImporter;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListBlueprints extends ListRecords
{
    protected static string $resource = BlueprintResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [

            Actions\CreateAction::make(),

            Actions\ActionGroup::make([

                ExportAction::make()
                    ->label(trans('Export Blueprints'))
                    ->exporter(BlueprintExporter::class)
                    ->chunkSize(500)
                    ->withActivityLog(
                        event: 'exported',
                        description: fn (ExportAction $action) => 'Exported '.$action->getModelLabel(),
                    ),

                ImportAction::make()
                    ->label(trans('Import Blueprints'))
                    ->importer(BlueprintImporter::class)
                    ->withActivityLog(
                        event: 'imported',
                        description: fn (ImportAction $action) => 'Imported '.$action->getModelLabel(),
                    ),
            ])->hidden(fn () => ! filament_admin()->hasRole(config()->string('domain.role.super_admin'))),

        ];
    }
}
