<?php

declare (strict_types = 1);

namespace App\FilamentTenant\Resources\CollectionResource\Pages;
use App\FilamentTenant\Resources\CollectionResource;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Domain\Collection\Models\CollectionEntry;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use App\Filament\Resources\ActivityResource\RelationManagers\ActivitiesRelationManager;
use Domain\Collection\Actions\UpdateCollectionEntryAction;
use Filament\Resources\RelationManagers\RelationGroup;

class EditCollectionEntry extends EditRecord
{
    protected static string $resource = CollectionResource::class;

    public $ownerRecord;

    /**
     * Override mount and 
     * call parent component mount.
     * 
     * @param mixed $record
     * 
     * @return void
     */
    public function mount($record): void 
    {
        $this->ownerRecord = static::getResource()::resolveRecordRouteBinding(Request::route('ownerRecord')); 
        $this->record = app(CollectionEntry::class)->resolveRouteBinding($record);

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException())->setModel($this->getModel(), [$key]);
        }

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    /**
     * @return string
     */
    protected function getTitle(): string
    {
        return trans('Edit :label Collection Entry', [
            'label' => $this->record->title,
        ]);
    }

    public function getModel(): string
    {
        return CollectionEntry::class;
    }

    /**
     * Build form from blueprint schema.
     * 
     * @return array
     */
    protected function getFormSchema(): array
    {
        return [
            Card::make([
                TextInput::make('title')
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('slug')
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (CollectionEntry $record) => $record !== null),
            ]),
            SchemaFormBuilder::make('data', fn () => $this->ownerRecord->blueprint->schema),
        ];
    }

    /**
     * @return array
     */
    protected function getRelationManagers(): array
    {
        $managers = [
            ActivitiesRelationManager::class
        ];

        return array_filter(
            $managers,
            function (string | RelationGroup $manager): bool {
                if ($manager instanceof RelationGroup) {
                    return (bool) count($manager->getManagers(ownerRecord: $this->getRecord()));
                }

                return $manager::canViewForRecord($this->getRecord());
            },
        );
    }

    /**
     * @param Model $record
     * @param array $data
     * 
     * @return Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app (UpdateCollectionEntryAction::class)
                ->execute($this->record, new CollectionEntryData(...$data))
        );
    }
}