<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlueprintResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\BlueprintResource;
use Domain\Blueprint\Actions\CreateBlueprintAction;
use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateBlueprint extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = BlueprintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament::resources/pages/create-record.form.actions.create.label'))
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
        return DB::transaction(fn () => app(CreateBlueprintAction::class)
            ->execute(new BlueprintData(
                name: $data['name'],
                schema: SchemaData::fromArray($data['schema']),
            )));
    }
}
