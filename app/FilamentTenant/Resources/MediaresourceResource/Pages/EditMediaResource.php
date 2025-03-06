<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\MediaresourceResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\MediaresourceResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Support\Common\Actions\SyncMediaCollectionAction;

class EditMediaResource extends EditRecord
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

    /**
     * @param  \Spatie\MediaLibrary\MediaCollections\Models\Media  $record
     */
    #[\Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(SyncMediaCollectionAction::class)->updateMedia($record, $data['custom_properties']);
    }
}
