<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\MenuResource\Pages;

use App\FilamentTenant\Resources\MenuResource;
use Domain\Menu\Exports\MenuExporter;
use Domain\Menu\Imports\MenuImporter;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                ExportAction::make()
                    ->label(trans('Export Menu'))
                    ->exporter(MenuExporter::class)
                    ->withActivityLog(
                        event: 'exported',
                        description: fn (ExportAction $action) => 'Exported '.$action->getModelLabel(),
                    ),
                ImportAction::make()
                    ->label(trans('Import Menu'))
                    ->importer(MenuImporter::class)
                    ->withActivityLog(
                        event: 'imported',
                        description: fn (ImportAction $action) => 'Imported '.$action->getModelLabel(),
                    ),
            ]),
        ];
    }
}
