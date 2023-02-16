<?php

namespace App\FilamentTenant\Resources\GlobalsResource\Pages;

use App\FilamentTenant\Resources\GlobalsResource;
use Domain\Globals\Actions\UpdateGlobalsAction;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Throwable;
use Exception;

class EditGlobals extends EditRecord
{
    protected static string $resource = GlobalsResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
      /**
     * @param \Domain\Globals\Models\Globals $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {

        return DB::transaction(
            fn () => app(UpdateGlobalsAction::class)
                ->execute(
                    $record,
                    new GlobalsData(
                        name: $data['name'],
                        slug: $data['slug'],
                        blueprint_id: $data['blueprint_id'],
                        data: $data['data'],
                    )
                )
        );
    }
}
