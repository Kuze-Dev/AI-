<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PaymentMethodResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\PaymentMethodResource;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Actions\GetAvailablePaymentDriverAction;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

/**
 * @property-read PaymentMethod $record
 */
class EditPaymentMethod extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = PaymentMethodResource::class;

    #[\Override]
    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! $this->hasGatewayDriver()) {
            Notification::make()
                ->warning()
                ->body(trans('Payment Gateway [:gateway] is currently Disabled please inform your service provider if you wish to Re Enabled this feature', [
                    'gateway' => $this->record->gateway,
                ]))
                ->send();
        }
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s'])
                ->visible($this->hasGatewayDriver()),
            Actions\DeleteAction::make()
                ->visible($this->hasGatewayDriver()),
        ];
    }

    private function hasGatewayDriver(): bool
    {
        return once(function () {
            $drivers = app(GetAvailablePaymentDriverAction::class)->execute();

            return array_key_exists($this->record->gateway, $drivers);
        });
    }
}
