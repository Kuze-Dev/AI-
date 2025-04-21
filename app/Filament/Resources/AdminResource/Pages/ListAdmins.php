<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Domain\Admin\Exports\AdminExporter;
use Domain\Admin\Imports\AdminImporter;
use Exception;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    /** @throws Exception */
    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(AdminImporter::class)
                ->withActivityLog(
                    event: 'imported',
                    description: fn (ImportAction $action) => 'Imported '.$action->getModelLabel(),
                ),
            ExportAction::make()
                ->exporter(AdminExporter::class)
                ->withActivityLog(
                    event: 'exported',
                    description: fn (ExportAction $action) => 'Exported '.$action->getModelLabel(),
                ),
            Actions\CreateAction::make(),
        ];
    }
}
