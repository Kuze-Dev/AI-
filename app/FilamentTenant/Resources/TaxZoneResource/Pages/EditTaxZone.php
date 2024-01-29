<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TaxZoneResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\TaxZoneResource;
use Domain\Taxation\Actions\UpdateTaxZoneAction;
use Domain\Taxation\DataTransferObjects\TaxZoneData;
use Domain\Taxation\Models\TaxZone;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property TaxZone $record
 */
class EditTaxZone extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = TaxZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    /** @param  TaxZone  $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateTaxZoneAction::class)->execute($record, TaxZoneData::formArray($data)));
    }
}
