<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TenantApiKeyResource\Pages;

use App\FilamentTenant\Resources\TenantApiKeyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTenantApiKeys extends ListRecords
{
    protected static string $resource = TenantApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
