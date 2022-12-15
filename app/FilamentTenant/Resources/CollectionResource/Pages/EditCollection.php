<?php 

declare (strict_types = 1);

namespace App\FilamentTenant\Resources\CollectionResource\Pages;

use App\FilamentTenant\Resources\CollectionResource;
use Domain\Collection\Actions\UpdateCollectionAction;
use Domain\Collection\DataTransferObjects\CollectionData;
use Filament\Resources\Pages\EditRecord;
use Filament\Pages\Actions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditCollection extends EditRecord 
{
    protected static string $resource = CollectionResource::class;

    protected function getActions(): array 
    {
        return [
            Actions\EditAction::make()
                ->url(route('filament-tenant.resources.' . self::$resource::getSlug() . '.edit', $this->record)),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getTitle(): string
    {
        return trans('Configure :label', [
            'label' => $this->getRecordTitle(),
        ]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app (UpdateCollectionAction::class)
                ->execute($record, new CollectionData(
                    name: $data['name'],
                    blueprint_id: (int) $data['blueprint_id'],
                    slug: $data['slug'],
                    display_publish_dates: $data['display_publish_dates'] == true ? 1 : 0,
                    past_publish_date: $data['past_publish_date'],
                    future_publish_date: $data['future_publish_date'],
                    is_sortable: (int) $data['is_sortable'],
                ))
        );
    }

    /**
     * @return string|null
     */
    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('configure', $this->record->slug);
    }
}