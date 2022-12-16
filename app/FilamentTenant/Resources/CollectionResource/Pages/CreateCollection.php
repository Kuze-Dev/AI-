<?php

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use Domain\Collection\Actions\CreateCollectionAction;
use Domain\Collection\DataTransferObjects\CollectionData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateCollection extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreateCollectionAction::class)
            ->execute(new CollectionData(...$data, 
                past_publish_date: $data['past_publish_date'] ?? '',
                future_publish_date: $data['future_publish_date'] ?? ''
            ))
        );
    }
}
