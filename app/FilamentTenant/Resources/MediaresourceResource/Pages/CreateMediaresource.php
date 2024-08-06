<?php

namespace App\FilamentTenant\Resources\MediaresourceResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\MediaresourceResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\Actions\Action;

class CreateMediaresource extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = MediaresourceResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('create')
                ->label(__('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }
}
