<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\MediaresourceResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\MediaresourceResource;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Support\Common\Actions\SyncMediaCollectionAction;

class EditMediaresource extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = MediaresourceResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
        ];
    }

    #[\Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media */
        $media = $record;

        return app(SyncMediaCollectionAction::class)->updateMedia($media, $data['custom_properties']);
    }

    #[\Override]
    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }
}
