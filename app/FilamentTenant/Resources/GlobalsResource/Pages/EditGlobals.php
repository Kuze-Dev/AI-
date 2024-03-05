<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\GlobalsResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\GlobalsResource;
use Domain\Globals\Actions\UpdateGlobalsAction;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditGlobals extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = GlobalsResource::class;

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

    /**
     * @param  \Domain\Globals\Models\Globals  $record
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateGlobalsAction::class)->execute($record, GlobalsData::fromArray($data));
    }
}
