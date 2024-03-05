<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ContentResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ContentEntryResource;
use App\FilamentTenant\Resources\ContentResource;
use Domain\Content\Actions\UpdateContentAction;
use Domain\Content\DataTransferObjects\ContentData;
use Domain\Content\Enums\PublishBehavior;
use Domain\Content\Models\Content;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditContent extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = ContentResource::class;

    /**
     * Declare action buttons that
     * are available on the page.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
            Actions\Action::make('view-entries')
                ->color('gray')
                ->record($this->getRecord())
                ->authorize(ContentEntryResource::canViewAny())
                ->url(ContentEntryResource::getUrl('index', [$this->getRecord()])),
        ];
    }

    /**
     * Execute database transaction
     * for updating contents.
     *
     * @param  Content  $record
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(UpdateContentAction::class)
            ->execute($record, new ContentData(
                name: $data['name'],
                taxonomies: $data['taxonomies'],
                blueprint_id: $data['blueprint_id'],
                is_sortable: $data['is_sortable'],
                past_publish_date_behavior: PublishBehavior::tryFrom($data['past_publish_date_behavior'] ?? ''),
                future_publish_date_behavior: PublishBehavior::tryFrom($data['future_publish_date_behavior'] ?? ''),
                prefix: $data['prefix'],
                sites: $data['sites'] ?? [],
            ));
    }
}
