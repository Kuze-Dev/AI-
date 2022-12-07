<?php 

namespace App\FilamentTenant\Resources\CollectionEntryResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use Domain\Taxonomy\Actions\UpdateCollectionEntryAction;
use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditCollectionEntry extends EditRecord 
{
    /**
     * @var string
     */
    protected static string $resource = CollectionResource::class;

    protected function getActions(): array
    {
        return [
            // 
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
            fn () => app (UpdateCollectionEntryAction::class)
                ->execute($record, new CollectionEntryData(
                    data: $data['data'],                    
                    collection_id: (int) $data['collection_id']
                ))
        );
    }
}