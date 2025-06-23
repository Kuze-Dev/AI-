<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ShippingmethodResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ShippingMethodResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateShippingxMethod extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = ShippingMethodResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }
}
