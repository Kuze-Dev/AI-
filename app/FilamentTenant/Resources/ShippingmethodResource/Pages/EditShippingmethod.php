<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ShippingmethodResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ShippingmethodResource;
use Domain\ShippingMethod\Actions\GetAvailableShippingDriverAction;
use Domain\ShippingMethod\Actions\UpdateShippingMethodAction;
use Domain\ShippingMethod\DataTransferObjects\ShippingMethodData;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class EditShippingmethod extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = ShippingmethodResource::class;

    protected function getHeaderActions(): array
    {

        $drivers = app(GetAvailableShippingDriverAction::class)->execute();

        /** @var \Domain\ShippingMethod\Models\ShippingMethod */
        $model = $this->record;

        if (array_key_exists($model->driver->value, $drivers)) {
            return [
                Action::make('save')
                    ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
                    ->action('save')
                    ->keyBindings(['mod+s']),
                Actions\DeleteAction::make(),
            ];
        }

        $this->notify('warning', 'Shipping Method ['.$model->driver->value.'] is currently Disabled please inform your service provider if you wish to Re Enabled this feature');

        return [];
    }

    /**
     * @param  \Domain\ShippingMethod\Models\ShippingMethod  $record
     *
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateShippingMethodAction::class)->execute($record, ShippingMethodData::fromArray($data)));
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }
}
