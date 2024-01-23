<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PaymentMethodResource\Pages;

use App\FilamentTenant\Resources\PaymentMethodResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentMethods extends ListRecords
{
    protected static string $resource = PaymentMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
