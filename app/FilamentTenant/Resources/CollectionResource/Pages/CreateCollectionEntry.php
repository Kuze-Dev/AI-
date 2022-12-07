<?php 

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use Domain\Collection\Actions\CreateCollectionEntryAction;
use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateCollectionEntry extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

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