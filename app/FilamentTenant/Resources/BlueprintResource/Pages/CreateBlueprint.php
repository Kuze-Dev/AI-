<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlueprintResource\Pages;

use App\FilamentTenant\Resources\BlueprintResource;
use Domain\Blueprint\Actions\CreateBlueprintAction;
use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateBlueprint extends CreateRecord
{
    protected static string $resource = BlueprintResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreateBlueprintAction::class)
            ->execute(new BlueprintData(
                name: $data['name'],
                schema: SchemaData::fromArray($data['schema']),
            )));
    }
}
