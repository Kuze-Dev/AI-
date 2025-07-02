<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\Pages;

use App\FilamentTenant\Resources\CustomerResource;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Exports\CustomerExporter;
use Exception;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    /** @throws Exception */
    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            // TODO: export only RegisterStatus::REGISTERED
            ExportAction::make()
                ->color('primary')
                ->icon('heroicon-o-arrow-down-tray')
                ->exporter(CustomerExporter::class)
                ->chuckSize(500)
//                ->authorize() // TODO: authorize customer export
                ->withActivityLog(
                    event: 'exported',
                    description: fn (ExportAction $action) => 'Exported '.$action->getModelLabel(),
                ),
            Actions\CreateAction::make(),
        ];
    }
}
