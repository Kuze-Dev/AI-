<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ShippingmethodResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ShippingMethodResource;
use Domain\ShippingMethod\Actions\GetAvailableShippingDriverAction;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

/**
 * @property-read \Domain\ShippingMethod\Models\ShippingMethod $record
 */
class EditShippingMethod extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = ShippingMethodResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! $this->hasValidShippingDriver()) {
            Notification::make()
                ->warning()
                ->body(trans('Shipping Method [:driver] is currently Disabled please inform your service provider if you wish to Re Enabled this feature', [
                    'driver' => $this->record->driver->value,
                ]))
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s'])
                ->visible($this->hasValidShippingDriver()),
            Actions\DeleteAction::make()
                ->visible($this->hasValidShippingDriver()),
        ];
    }

    private function hasValidShippingDriver(): bool
    {
        return once(function () {
            $drivers = app(GetAvailableShippingDriverAction::class)->execute();

            return array_key_exists($this->record->driver->value, $drivers);
        });
    }
}
