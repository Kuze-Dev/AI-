<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlueprintResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\BlueprintResource;
use Domain\Blueprint\Actions\UpdateBlueprintAction;
use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Domain\Blueprint\Models\Blueprint;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditBlueprint extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = BlueprintResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /** @param Blueprint $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateBlueprintAction::class)
            ->execute(
                $record,
                new BlueprintData(
                    name: $data['name'],
                    schema: SchemaData::fromArray($data['schema']),
                )
            ));
    }

    protected function afterSave(): void
    {
        $this->record = $this->resolveRecord($this->record->getRouteKey());

        $this->fillForm();
    }
}
