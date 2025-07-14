<?php

namespace App\FilamentTenant\Resources\TenantApiKeyResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\TenantApiKeyResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateTenantApiKey extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = TenantApiKeyResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('Create'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

}
