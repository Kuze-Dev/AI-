<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlueprintResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\BlueprintResource;
use Domain\Blueprint\Actions\CreateBlueprintAction;
use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBlueprint extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = BlueprintResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    #[\Override]
    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateBlueprintAction::class)
            ->execute(new BlueprintData(
                name: $data['name'],
                schema: SchemaData::fromArray($data['schema']),
            ));
    }
}
