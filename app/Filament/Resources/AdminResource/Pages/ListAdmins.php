<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Domain\Admin\Actions\CreateAdminAction;
use Domain\Admin\Actions\UpdateAdminAction;
use Domain\Admin\DataTransferObjects\AdminData;
use Domain\Admin\Exports\AdminExporter;
use Domain\Admin\Imports\AdminImporter;
use Domain\Admin\Models\Admin;
use Domain\Role\Models\Role;
use Exception;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\ValidationRules\Rules\Delimited;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    /** @throws Exception */
    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(AdminImporter::class)
                ->withActivityLog(
                    event: 'imported',
                    description: fn (ExportAction $action) => 'Imported '.$action->getModelLabel(),
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
