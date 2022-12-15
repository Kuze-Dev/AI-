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
        $parent_key = Request::route('record');
        $record_key = Request::route('ownerRecord');

        $this->ownerRecord = static::getResource()::resolveRecordRouteBinding($parent_key);
        $this->record = $record_key; 

        parent::mount($parent_key);
    }

    /**
     * @return string
     */
    protected function getTitle(): string
    {
        return trans('Edit :label Collection Entry', [
            'label' => $this->ownerRecord->name,
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
     * @param Model $record
     * @param array $data
     * 
     * @return Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app (EditCollectionEntryAction::class)
                ->execute($this->ownerRecord, new CollectionEntryData(...$data))
        );
    }
}