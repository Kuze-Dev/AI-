<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceOrderResource;
use Domain\ServiceOrder\Actions\PlaceServiceOrderAction;
use Domain\ServiceOrder\DataTransferObjects\PlaceServiceOrderData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateServiceOrder extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = ServiceOrderResource::class;

    public function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(PlaceServiceOrderAction::class)
                ->execute(
                    new PlaceServiceOrderData(
                        customer_id: $data['customer_id'],
                        service_id: $data['service_id'],
                        schedule: $data['schedule'],
                        service_address_id: $data['service_address_id'],
                        billing_address_id: $data['billing_address_id'],
                        is_same_as_billing: $data['is_same_as_billing'],
                        additional_charges: $data['additional_charges'],
                        form: $data['form'],
                    )
                )
        );
    }

    protected function getActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }
}
