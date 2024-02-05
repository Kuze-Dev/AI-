<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\Filament\Resources\TenantResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }
}
