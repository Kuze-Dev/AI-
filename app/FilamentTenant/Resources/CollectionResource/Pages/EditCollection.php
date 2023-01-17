<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use Domain\Collection\Actions\UpdateCollectionAction;
use Domain\Collection\DataTransferObjects\CollectionData;
use Domain\Collection\Enums\PublishBehavior;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\Actions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Domain\Collection\Models\Collection;

class EditCollection extends EditRecord
{
    protected static string $resource = CollectionResource::class;

    /**
     * Declare action buttons that
     * are available on the page.
     */
    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Execute database transaction
     * for updating collections.
     * @param Collection $record
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(UpdateCollectionAction::class)
                ->execute($record, new CollectionData(
                    name: $data['name'],
                    taxonomies: $data['taxonomies'],
                    blueprint_id: $data['blueprint_id'],
                    slug: $data['slug'],
                    is_sortable: $data['is_sortable'],
                    past_publish_date_behavior: PublishBehavior::tryFrom($data['past_publish_date_behavior'] ?? ''),
                    future_publish_date_behavior: PublishBehavior::tryFrom($data['future_publish_date_behavior'] ?? ''),
                ))
        );
    }
}
