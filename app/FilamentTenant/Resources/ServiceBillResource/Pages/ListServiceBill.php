<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceBillResource\Pages;

use App\FilamentTenant\Resources\ServiceBillResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceBill extends ListRecords
{
    protected static string $resource = ServiceBillResource::class;

    protected function getActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
