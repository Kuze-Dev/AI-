<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlueprintResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\BlueprintResource;
use Domain\Blueprint\Actions\UpdateBlueprintAction;
use Domain\Blueprint\DataTransferObjects\BlueprintData;
use Domain\Blueprint\DataTransferObjects\FieldData;
use Domain\Blueprint\DataTransferObjects\RepeaterFieldData;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Models\BlueprintData as ModelsBlueprintData;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

class EditBlueprint extends EditRecord
{
    use LogsFormActivity {
        afterSave as protected afterSaveOverride;
    }

    protected static string $resource = BlueprintResource::class;

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

    /** @param  Blueprint  $record */
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
        $blueprinDataCollection = ModelsBlueprintData::where('blueprint_id', $this->record->getRouteKey())->with('media')->get();
        foreach ($blueprinDataCollection as $blueprintData) {
            $mediaCollection = $blueprintData->media;
            $sections = $this->record->getAttribute('schema')->sections;
            foreach ($sections as $section) {
                foreach ($section->fields as $field) {
                    $this->extractSchema($field, $section->state_name, $blueprintData->state_path, $mediaCollection);
                }
            }
        }
        // Artisan::call('media-library:regenerate');

        $this->afterSaveOverride();

        $this->record = $this->resolveRecord($this->record->getRouteKey());

        $this->fillForm();
    }

    /**
     * @param  \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media>  $mediaCollection
     */
    protected function extractSchema(RepeaterFieldData|FieldData $field, string $currentpath, string $state_path, MediaCollection $mediaCollection): void
    {

        $statePath = $currentpath.'.'.$field->state_name;
        if ($field->type === FieldType::REPEATER) {
            if (property_exists($field, 'fields') && is_array($field->fields)) {
                foreach ($field->fields as $repeaterFields) {
                    $this->extractSchema($repeaterFields, $statePath, $state_path, $mediaCollection);
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
                $newMediaConversion = [];
                foreach ($mediaCollection as $media) {
                    if (property_exists($field, 'conversions')) {
                        foreach ($field->conversions as $conversion) {
                            if (array_key_exists($conversion->name, $media->generated_conversions)) {
                                $newMediaConversion[$conversion->name] = true;
                            }
                        }
                    }
                }
                foreach ($mediaCollection as $media) {
                    $media->generated_conversions = $newMediaConversion;
                    $media->save();
                }
            }
        }

    }
}
