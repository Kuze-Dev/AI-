<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CountryResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\CountryResource;
use Domain\Address\Actions\UpdateCountryAction;
use Domain\Address\DataTransferObjects\CountryData;
use Domain\Address\Models\Country;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditCountry extends EditRecord
{
    use LogsFormActivity {
        afterSave as protected afterSaveOverride;
    }

    protected static string $resource = CountryResource::class;

    protected function getRedirectUrl(): ?string
    {
        return CountryResource::getUrl('index');
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

    /** @param Country $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // dd($data);
        return DB::transaction(
            fn () => app(UpdateCountryAction::class)
                ->execute(
                    $record,
                    new CountryData(
                        code: $data['code'],
                        name: $data['name'],
                        capital: $data['capital'],
                        timezone: $data['timezone'],
                        state_or_province: $data['state_or_province'],
                        language: $data['language'],
                        active: $data['active'],
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
