<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\CollectionEntryResource\Pages;

use App\FilamentTenant\Resources\CollectionEntryResource;
use App\FilamentTenant\Resources\CollectionResource;
use Carbon\Carbon;
use Domain\Collection\Actions\CreateCollectionEntryAction;
use Domain\Collection\DataTransferObjects\CollectionEntryData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Domain\Collection\Models\Collection;

class CreateCollectionEntry extends CreateRecord
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

    public function getBreadcrumb(): string
    {
        return trans('Create :label Collection Entry', ['label' => $this->ownerRecord->name]);
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

    protected function getTitle(): string
    {
        return trans('Create :label Collection Entry', [
            'label' => $this->ownerRecord->name,
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreateCollectionEntryAction::class)
                ->execute($this->ownerRecord, new CollectionEntryData(
                    title: $data['title'],
                    slug: $data['slug'],
                    published_at: isset($data['published_at']) ? Carbon::parse($data['published_at']) : null,
                    taxonomy_terms: $data['taxonomy_terms'] ?? [],
                    data: $data['data']
                ))
        );
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();

        if ($resource::hasPage('view') && $resource::canView($this->record)) {
            return $resource::getUrl('view', [$this->record]);
        }

        if ($resource::hasPage('edit') && $resource::canEdit($this->record)) {
            return $resource::getUrl('edit', [$this->ownerRecord, $this->record]);
        }

        return $resource::getUrl('index', [$this->ownerRecord]);
    }
}
