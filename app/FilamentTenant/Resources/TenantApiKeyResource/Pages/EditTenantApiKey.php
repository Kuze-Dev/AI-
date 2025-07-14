<?php

namespace App\FilamentTenant\Resources\TenantApiKeyResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\TenantApiKeyResource;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditTenantApiKey extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = TenantApiKeyResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }
}
