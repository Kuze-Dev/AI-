<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PaymentMethodResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\PaymentMethodResource;
use Domain\PaymentMethod\Actions\UpdatePaymentMethodAction;
use Domain\PaymentMethod\DataTransferObjects\PaymentMethodData;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Domain\PaymentMethod\Models\PaymentMethod;
use Throwable;

class EditPaymentMethod extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = PaymentMethodResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /**
     * @param PaymentMethod $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdatePaymentMethodAction::class)->execute($record, PaymentMethodData::fromArray($data)));
    }
}
