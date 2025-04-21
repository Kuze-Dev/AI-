<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxZoneResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\TaxZoneResource;
use Domain\Taxation\Actions\UpdateTaxZoneAction;
use Domain\Taxation\DataTransferObjects\TaxZoneData;
use Domain\Taxation\Models\TaxZone;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read TaxZone $record
 */
class EditTaxZone extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = TaxZoneResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    /** @param  TaxZone  $record */
    #[\Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateTaxZoneAction::class)->execute($record, TaxZoneData::formArray($data));
    }
}
