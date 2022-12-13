<?php 

namespace App\FilamentTenant\Resources\CollectionEntryResource\Pages;

use App\FilamentTenant\Resources\CollectionEntryResource;
use Domain\Collection\Actions\CreateCollectionEntryAction;
use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateCollectionEntry extends CreateRecord
{
    protected static string $resource = CollectionEntryResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreateCollectionEntryAction::class)
                ->execute(new CollectionEntryData(
                    collection_id: (int) $data['collection_id'],
                    data: $data['data']
                ))
        );
    }
}