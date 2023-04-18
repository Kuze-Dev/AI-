<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ContentEntryResource\Pages;

use App\FilamentTenant\Resources\ContentEntryResource;
use App\FilamentTenant\Resources\ContentResource;
use Domain\Content\Models\Content;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class ListContentEntry extends ListRecords
{
    protected static string $resource = ContentEntryResource::class;

    public mixed $ownerRecord;

    public function mount(string $ownerRecord = ''): void
    {
        $this->ownerRecord = app(Content::class)->resolveRouteBinding($ownerRecord)?->load('taxonomies.taxonomyTerms');

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException())->setModel(Content::class, ['']);
        }

        parent::mount();
    }

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label(trans('Edit Content'))
                ->url(ContentResource::getUrl('edit', [$this->ownerRecord])),
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
                ContentResource::getUrl('index') => ContentResource::getBreadcrumb(),
                ContentResource::getUrl('edit', [$this->ownerRecord]) => $this->ownerRecord->name,
                $resource::getUrl('index', [$this->ownerRecord]) => $resource::getBreadcrumb(),
            ],
            (filled($breadcrumb) ? [$breadcrumb] : []),
        );
    }

    protected function isTableReorderable(): bool
    {
        return $this->ownerRecord->is_sortable;
    }

    /** @return Builder<\Domain\Content\Models\ContentEntry> */
    protected function getTableQuery(): Builder
    {
        return $this->ownerRecord->contentEntries()->getQuery()->with('activeRouteUrl');
    }
}
