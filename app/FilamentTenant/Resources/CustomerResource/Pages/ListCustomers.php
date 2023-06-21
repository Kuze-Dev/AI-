<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\Pages;

use App\FilamentTenant\Resources\CustomerResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Exception;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
