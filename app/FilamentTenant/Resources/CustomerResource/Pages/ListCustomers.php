<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\Pages;

use App\FilamentTenant\Resources\CustomerResource;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Exports\CustomerExporter;
use Exception;
use Filament\Actions\ExportAction;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    /** @throws Exception */
    protected function getHeaderActions(): array
    {
        return [
            // TODO: export only RegisterStatus::REGISTERED
            ExportAction::make()
                ->exporter(CustomerExporter::class)
//                ->authorize() // TODO: authorize customer export
                ->withActivityLog(
                    event: 'exported',
                    description: fn (ExportAction $action) => 'Exported '.$action->getModelLabel(),
                ),
            Actions\CreateAction::make(),
        ];
    }
}
