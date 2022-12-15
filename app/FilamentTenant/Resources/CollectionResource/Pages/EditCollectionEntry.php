<?php

declare (strict_types = 1);

namespace App\FilamentTenant\Resources\CollectionResource\Pages;
use App\FilamentTenant\Resources\CollectionResource;
use App\FilamentTenant\Support\SchemaFormBuilder;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditCollectionEntry extends EditRecord
{
    protected static string $resource = CollectionResource::class;

    public $ownerRecord;

    protected function getTitle(): string
    {
        return trans('Edit :label Collection Entry', [
            'label' => $this->ownerRecord->name,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            SchemaFormBuilder::make('data', fn () => $this->ownerRecord->blueprint->schema),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app (CreateCollectionEntryAction::class)
                ->execute()
        );
    }
}