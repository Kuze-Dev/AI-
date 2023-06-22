<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CountryResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\CountryResource;
use Domain\Country\Actions\CreateCountryAction;
use Domain\Country\DataTransferObjects\CountryData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateCountry extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = CountryResource::class;

    

   
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
        return DB::transaction(fn () => app(CreateCountryAction::class)
            ->execute(new CountryData(
                code: $data['code'],
                name: $data['name'],
                capital: $data['capital'],
                timezone: $data['timezone'],
                language: $data['language'],
                active: $data['active'],
            )));
    }
}
