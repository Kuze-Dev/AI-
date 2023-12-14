<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceOrderResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceOrderResource;
use Domain\ServiceOrder\Actions\CreateServiceOrderAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
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
            fn () => app(CreateServiceOrderAction::class)
                ->execute(ServiceOrderData::fromArray($data))
        );
    }

    protected function getActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }
}
