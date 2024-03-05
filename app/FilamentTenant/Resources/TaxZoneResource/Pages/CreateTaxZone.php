<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxZoneResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\TaxZoneResource;
use Domain\Taxation\Actions\CreateTaxZoneAction;
use Domain\Taxation\DataTransferObjects\TaxZoneData;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTaxZone extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = TaxZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateTaxZoneAction::class)->execute(TaxZoneData::formArray($data));
    }
}
