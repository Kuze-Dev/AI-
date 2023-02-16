<?php

namespace App\FilamentTenant\Resources\GlobalsResource\Pages;

use App\FilamentTenant\Resources\GlobalsResource;
use Illuminate\Database\Eloquent\Model;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateGlobals extends CreateRecord
{
    protected static string $resource = GlobalsResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        dd($data);
        // return DB::transaction(
        //     fn () => app(CreateCollectionAction::class)
        //         ->execute(new CollectionData(
        //             name: $data['name'],
        //             slug: $data['slug'],
        //             taxonomies: $data['taxonomies'],
        //             blueprint_id: $data['blueprint_id'],
        //             is_sortable: $data['is_sortable'],
        //             past_publish_date_behavior: PublishBehavior::tryFrom($data['past_publish_date_behavior'] ?? ''),
        //             future_publish_date_behavior: PublishBehavior::tryFrom($data['future_publish_date_behavior'] ?? ''),
        //             route_url: $data['route_url'],
        //         ))
        // );
    }
}
