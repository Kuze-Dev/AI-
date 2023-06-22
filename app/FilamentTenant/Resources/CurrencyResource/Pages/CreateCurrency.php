<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CurrencyResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\CurrencyResource;
use Domain\Currency\Actions\CreateCurrencyAction;
use Domain\Currency\DataTransferObjects\CurrencyData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateCurrency extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = CurrencyResource::class;

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        return $resource::getUrl('index');
    }

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

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreateCurrencyAction::class)
            ->execute(new CurrencyData(
                code: $data['code'],
                name: $data['name'],
                enabled: $data['enabled'],
                exchange_rate: (float) $data['exchange_rate'],
                default: $data['default'],
            )));
    }
}
