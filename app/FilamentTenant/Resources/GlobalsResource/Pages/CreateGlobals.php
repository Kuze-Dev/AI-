<?php

namespace App\FilamentTenant\Resources\GlobalsResource\Pages;

use App\FilamentTenant\Resources\GlobalsResource;
use Domain\Globals\Actions\CreateGlobalsAction;
use Domain\Globals\DataTransferObjects\GlobalsData;
use Illuminate\Database\Eloquent\Model;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateGlobals extends CreateRecord
{
    protected static string $resource = GlobalsResource::class;

    protected function handleRecordCreation(array $data): Model
    {
   
        return DB::transaction(
            fn() => app(CreateGlobalsAction::class)->execute(
                new GlobalsData(
                    name: $data['name'],
                    slug: $data['slug'],
                    blueprint_id: $data['blueprint_id'],
                    data: $data['data'],
                ))
            );
    }
}
