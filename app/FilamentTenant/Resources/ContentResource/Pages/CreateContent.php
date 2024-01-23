<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ContentResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ContentResource;
use Domain\Content\Actions\CreateContentAction;
use Domain\Content\DataTransferObjects\ContentData;
use Domain\Content\Enums\PublishBehavior;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateContent extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /**
     * Execute database transaction
     * for creating contents.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreateContentAction::class)
                ->execute(new ContentData(
                    name: $data['name'],
                    taxonomies: $data['taxonomies'],
                    blueprint_id: $data['blueprint_id'],
                    is_sortable: $data['is_sortable'],
                    past_publish_date_behavior: PublishBehavior::tryFrom($data['past_publish_date_behavior'] ?? ''),
                    future_publish_date_behavior: PublishBehavior::tryFrom($data['future_publish_date_behavior'] ?? ''),
                    prefix: $data['prefix'],
                    sites: $data['sites'] ?? [],
                ))
        );
    }
}
