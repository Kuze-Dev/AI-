<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionEntryResource\Pages;

use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\FilamentTenant\Resources\CollectionEntryResource;
use App\FilamentTenant\Resources\CollectionResource;
use Carbon\Carbon;
use Domain\Collection\Actions\UpdateCollectionEntryAction;
use Domain\Collection\Models\Collection;
use Filament\Pages\Actions;
use Filament\Pages\Actions\DeleteAction;

/** @method class-string<\Illuminate\Database\Eloquent\Model> getModel() */
class EditCollectionEntry extends EditRecord
{
    protected static string $resource = CollectionEntryResource::class;

    public mixed $ownerRecord;

    /**
     * Override mount and
     * call parent component mount.
     *
     * @param mixed $record
     *
     * @return void
     */
    public function mount($record, string $ownerRecord = ''): void
    {
        $this->ownerRecord = app(Collection::class)
            ->resolveRouteBinding($ownerRecord)
            ?->load('taxonomies.taxonomyTerms');

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException())->setModel(Collection::class, ['']);
        }

        parent::mount($record);
    }

    /** @param string $key */
    protected function resolveRecord($key): Model
    {
        $record = $this->ownerRecord->resolveChildRouteBinding('collectionEntries', $key, null);

        if ($record === null) {
            throw (new ModelNotFoundException())->setModel($this->getModel(), [$key]);
        }

        return $record;
    }

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function configureDeleteAction(DeleteAction $action): void
    {
        $resource = static::getResource();

        $action
            ->authorize($resource::canDelete($this->getRecord()))
            ->record($this->getRecord())
            ->recordTitle($this->getRecordTitle())
            ->successRedirectUrl(static::getResource()::getUrl('index', [$this->ownerRecord]));
    }

    protected function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumb = $this->getBreadcrumb();

        return array_merge(
            [
                CollectionResource::getUrl('index') => CollectionResource::getBreadcrumb(),
                CollectionResource::getUrl('edit', ['record' => $this->ownerRecord]) => $this->ownerRecord->name,
                $resource::getUrl('index', [$this->ownerRecord]) => $resource::getBreadcrumb(),
                $this->getRecordTitle(),
            ],
            (filled($breadcrumb) ? [$breadcrumb] : []),
        );
    }

    /**
     * Execute database transaction
     * for updating collection entries.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(UpdateCollectionEntryAction::class)
                ->execute($this->record, new CollectionEntryData(
                    title: $data['title'],
                    slug: $data['slug'],
                    taxonomy_terms: $data['taxonomy_terms'] ?? [],
                    published_at: isset($data['published_at']) ? Carbon::parse($data['published_at']) : null,
                    data: $data['data']
                ))
        );
    }
}
