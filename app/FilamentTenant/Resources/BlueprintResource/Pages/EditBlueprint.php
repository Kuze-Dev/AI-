<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlueprintResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\BlueprintResource;
use Domain\Blueprint\Actions\UpdateBlueprintAction;
use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\DataTransferObjects\ConversionData;
use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\DataTransferObjects\RepeaterFieldData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Models\BlueprintData as ModelsBlueprintData;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBlueprint extends EditRecord
{
    use LogsFormActivity {
        afterSave as protected afterSaveOverride;
    }

    protected static string $resource = BlueprintResource::class;

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

    /** @param  Blueprint  $record */
    #[\Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateBlueprintAction::class)
            ->execute(
                $record,
                new BlueprintData(
                    name: $data['name'],
                    schema: SchemaData::fromArray($data['schema']),
                )
            );
    }

    protected function afterSave(): void
    {
        $blueprinDataCollection = ModelsBlueprintData::where('blueprint_id', $this->record->getRouteKey())->with('media')->get();

        foreach ($blueprinDataCollection as $blueprintData) {

            $sections = $this->record->getAttribute('schema')->sections;
            foreach ($sections as $section) {
                foreach ($section->fields as $field) {
                    $this->extractSchema($field, $section->state_name, $blueprintData->state_path, $blueprintData);
                }
            }
        }

        $this->afterSaveOverride();

        $this->record = $this->resolveRecord($this->record->getRouteKey());

        $this->fillForm();
    }

    protected function extractSchema(RepeaterFieldData|FieldData $field, string $currentpath, string $state_path, ModelsBlueprintData $blueprintData): void
    {

        $statePath = $currentpath.'.'.$field->state_name;
        if ($field->type === FieldType::REPEATER) {
            if (property_exists($field, 'fields') && is_array($field->fields)) {
                foreach ($field->fields as $repeaterFields) {
                    $this->extractSchema($repeaterFields, $statePath, $state_path, $blueprintData);
                }
            }
        }
        if ($field->type === FieldType::MEDIA) {
            $arrayStatepath = explode('.', $state_path);
            foreach ($arrayStatepath as $newStatepath) {
                if (is_numeric($newStatepath)) {
                    $arrayStatepath = array_diff($arrayStatepath, [$newStatepath]);
                }
            }
            $newStatepath = implode('.', $arrayStatepath);
            if ($statePath === $newStatepath) {

                /** @var \Domain\Blueprint\DataTransferObjects\MediaFieldData */
                $mediaField = $field;

                $conversions = $mediaField->conversions;

                $savedConversions = array_map(
                    fn (array $conversion) => ConversionData::fromArray($conversion),
                    $blueprintData->blueprint_media_conversion ?? []
                );

                if (serialize($conversions) !== serialize($savedConversions) || is_null($blueprintData->blueprint_media_conversion)) {

                    app(\Domain\Blueprint\Jobs\UpdateBlueprintDataMediaConversionJob::class)->dispatch(
                        $blueprintData,
                        $conversions
                    );

                }

            }
        }

    }
}
