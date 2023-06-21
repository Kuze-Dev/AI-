<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PaymentMethodResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\PaymentMethodResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\Actions\Action;

class CreatePaymentMethod extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = PaymentMethodResource::class;

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
