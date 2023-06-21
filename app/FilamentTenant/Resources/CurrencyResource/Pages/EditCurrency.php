<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CurrencyResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\CurrencyResource;
use Domain\Currency\Actions\UpdateCurrencyAction;
use Domain\Currency\DataTransferObjects\CurrencyData;
use Domain\Currency\Models\Currency;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditCurrency extends EditRecord
{
    use LogsFormActivity {
        afterSave as protected afterSaveOverride;
    }

    protected static string $resource = CurrencyResource::class;

    protected function getRedirectUrl(): ?string
    {
        return CurrencyResource::getUrl('index');
    }

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

    /** @param Currency $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // dd($data);
        return DB::transaction(fn() => app(UpdateCurrencyAction::class)
            ->execute(
                $record,
                new CurrencyData(
                    code: $data['code'],
                    name: $data['name'],
                    enabled: $data['enabled'],
                    exchange_rate: (float) $data['exchange_rate'],
                    default: $data['default'],
                )
            )
        );
    }

    protected function afterSave(): void
    {
        $this->afterSaveOverride();

        $this->record = $this->resolveRecord($this->record->getRouteKey());

        $this->fillForm();
    }
}