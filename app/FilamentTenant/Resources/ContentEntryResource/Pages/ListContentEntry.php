<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ContentEntryResource\Pages;

use App\Features\CMS\SitesManagement;
use App\FilamentTenant\Resources\ContentEntryResource;
use App\FilamentTenant\Resources\ContentResource;
use App\FilamentTenant\Support\Traits\HasParentResource;
use Domain\Content\Models\Content;
use Domain\Tenant\TenantFeatureSupport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ListContentEntry extends ListRecords
{
    // use HasParentResource;
    
    protected static string $resource = ContentEntryResource::class;

    public mixed $ownerRecord;


    #[\Override]
    public function mount(string $ownerRecord = ''): void
    {
   
        $this->ownerRecord = app(Content::class)->resolveRouteBinding($ownerRecord)?->load('taxonomies.taxonomyTerms');

        if ($this->ownerRecord === null) {
            throw (new ModelNotFoundException())->setModel(Content::class, ['']);
        }

        parent::mount();
    }

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make()
            //     ->label(trans('Edit Content'))
            //     ->visible(ContentResource::canEdit($this->ownerRecord))
            //     ->url(ContentResource::getUrl('edit', [$this->ownerRecord])),
            // Actions\CreateAction::make()
            //     ->label(trans('Create entry'))
            //     ->url(self::getResource()::getUrl('create', [$this->ownerRecord])),

            // Actions\CreateAction::make()
            // ->url(
            //     fn (): string => static::getParentResource()::getUrl('entries.create', [
            //         'ownerRecord' => $this->ownerRecord,
            //     ])
            // ),


        ];
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->ownerRecord->name.' '.Str::headline(static::getResource()::getPluralModelLabel());
    }

    #[\Override]
    public function getBreadcrumbs(): array
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
    #[\Override]
    protected function getTableQuery(): Builder
    {

        if (Auth::user()?->hasRole(config('domain.role.super_admin'))) {
            return $this->ownerRecord->contentEntries()->getQuery();
        }

        if (TenantFeatureSupport::active(SitesManagement::class) &&
            Auth::user()?->can('site.siteManager') &&
            ! (Auth::user()->hasRole(config('domain.role.super_admin')))
        ) {
            return $this->ownerRecord->contentEntries()->getQuery()->wherehas('sites', fn ($q) => $q->whereIn('site_id', Auth::user()?->userSite->pluck('id')->toArray()));
        }

        return $this->ownerRecord->contentEntries()->getQuery();
    }
}
