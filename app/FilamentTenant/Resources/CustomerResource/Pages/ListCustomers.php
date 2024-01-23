<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\Pages;

use App\FilamentTenant\Resources\CustomerResource;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Export\Exports;
use Exception;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

//use Support\Excel\Actions\ExportAction;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    /** @throws Exception */
    protected function getHeaderActions(): array
    {
        return [
            Exports::headerList([RegisterStatus::REGISTERED]),
            Actions\CreateAction::make(),
        ];
    }
}
