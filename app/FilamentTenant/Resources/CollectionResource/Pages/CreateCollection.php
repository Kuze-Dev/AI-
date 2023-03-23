<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use Domain\Collection\Actions\CreateCollectionAction;
use Domain\Collection\DataTransferObjects\CollectionData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Domain\Collection\Enums\PublishBehavior;
use Filament\Pages\Actions\Action;

class CreateCollection extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('create')
                ->label(__('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
            $this->getCreateAnotherFormAction(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }
    
    /**
     * Execute database transaction
     * for creating collections.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreateCollectionAction::class)
                ->execute(new CollectionData(
                    name: $data['name'],
                    slug: $data['slug'],
                    taxonomies: $data['taxonomies'],
                    blueprint_id: $data['blueprint_id'],
                    is_sortable: $data['is_sortable'],
                    past_publish_date_behavior: PublishBehavior::tryFrom($data['past_publish_date_behavior'] ?? ''),
                    future_publish_date_behavior: PublishBehavior::tryFrom($data['future_publish_date_behavior'] ?? ''),
                    route_url: $data['route_url'],
                ))
        );
    }
}
