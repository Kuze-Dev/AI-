<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ShippingmethodResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ShippingmethodResource;
use Domain\ShippingMethod\Actions\CreateShippingMethodAction;
use Domain\ShippingMethod\DataTransferObjects\ShippingMethodData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateShippingmethod extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = ShippingmethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreateShippingMethodAction::class)->execute(ShippingMethodData::fromArray($data)));
    }
}
