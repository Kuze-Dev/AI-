<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionEntryResource\Pages;

use App\FilamentTenant\Resources\CollectionEntryResource;
use App\FilamentTenant\Resources\CollectionResource;
use Domain\Collection\Models\Collection;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class ListCollectionEntry extends ListRecords
{
    protected static string $resource = CollectionEntryResource::class;

    public mixed $ownerRecord;

    public function mount(string $ownerRecord = ''): void
    {
        $this->ownerRecord = app(Collection::class)->resolveRouteBinding($ownerRecord)?->load('taxonomies.taxonomyTerms');

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException())->setModel(Collection::class, ['']);
        }

        parent::mount();
    }

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label(trans('Edit Collection'))
                ->url(CollectionResource::getUrl('edit', [$this->ownerRecord])),
            Actions\CreateAction::make()
                ->label(trans('Create entry'))
                ->url(self::getResource()::getUrl('create', [$this->ownerRecord])),
        ];
    }

    protected function getTitle(): string
    {
        return $this->ownerRecord->name . ' ' . Str::headline(static::getResource()::getPluralModelLabel());
    }

    protected function getBreadcrumbs(): array
    {
        $resource = static::getResource();

        $breadcrumb = $this->getBreadcrumb();

        return array_merge(
            [
                CollectionResource::getUrl('index') => CollectionResource::getBreadcrumb(),
                CollectionResource::getUrl('edit', [$this->ownerRecord]) => $this->ownerRecord->name,
                $resource::getUrl('index', [$this->ownerRecord]) => $resource::getBreadcrumb(),
            ],
            (filled($breadcrumb) ? [$breadcrumb] : []),
        );
    }

    protected function isTableReorderable(): bool
    {
        return $this->ownerRecord->is_sortable;
    }

    /** @return Builder<\Domain\Collection\Models\CollectionEntry> */
    protected function getTableQuery(): Builder
    {
        return $this->ownerRecord->collectionEntries()->getQuery()->with('routeUrls');
    }
}
