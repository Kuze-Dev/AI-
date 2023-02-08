<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use Domain\Collection\Actions\CreateCollectionAction;
use Domain\Support\MetaTag\Actions\CreateMetaTagsAction;
use Domain\Collection\DataTransferObjects\CollectionData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Domain\Collection\Enums\PublishBehavior;
use Domain\Support\MetaTag\DataTransferObjects\MetaTagData;

class CreateCollection extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

    /**
     * Execute database transaction
     * for creating collections.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $collection = app(CreateCollectionAction::class)
                ->execute(new CollectionData(
                    name: $data['name'],
                    slug: $data['slug'],
                    taxonomies: $data['taxonomies'],
                    blueprint_id: $data['blueprint_id'],
                    is_sortable: $data['is_sortable'],
                    past_publish_date_behavior: PublishBehavior::tryFrom($data['past_publish_date_behavior'] ?? ''),
                    future_publish_date_behavior: PublishBehavior::tryFrom($data['future_publish_date_behavior'] ?? ''),
                    route_url: $data['route_url'],
                ));
            app(CreateMetaTagsAction::class)
                ->execute(new MetaTagData(
                    model : $collection,
                    meta_title: $data['meta_title'],
                    meta_author: $data['meta_author'],
                    meta_description: $data['meta_description'],
                    meta_keywords: $data['meta_keywords']
                ));

            return $collection;
        });

        // fn () => app(CreateCollectionAction::class)
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
    }
}
