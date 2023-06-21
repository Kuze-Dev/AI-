<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CustomerResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\CustomerResource;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = CustomerResource::class;

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
