<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use App\Features\CMS\SitesManagement;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Models\ContentEntry;
use Domain\Internationalization\Models\Locale;
use Domain\Tenant\TenantFeatureSupport;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\MetaData\Actions\UpdateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class UpdateContentEntryAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {
    }

    /**
     * Execute operations for updating
     * and save content entry query.
     */
    public function execute(ContentEntry $contentEntry, ContentEntryData $contentEntryData): ContentEntry
    {
        $contentEntry->update([
            'author_id' => $contentEntryData->author_id,
            'title' => $contentEntryData->title,
            'published_at' => $contentEntryData->published_at,
            'data' => $contentEntryData->data,
            'locale' => $contentEntryData->locale ?? Locale::where('is_default', true)->first()?->code,
        ]);

        $contentEntry->metaData()->exists()
            ? $this->updateMetaData->execute($contentEntry, $contentEntryData->meta_data)
            : $this->createMetaData->execute($contentEntry, $contentEntryData->meta_data);

        $contentEntry->taxonomyTerms()
            ->sync($contentEntryData->taxonomy_terms);

        $this->createOrUpdateRouteUrl->execute($contentEntry, $contentEntryData->route_url_data);

        if (TenantFeatureSupport::active(SitesManagement::class)) {

            $contentEntry->sites()->sync($contentEntryData->sites);
        }

        $this->updateBlueprintDataAction->execute($contentEntry);

        return $contentEntry;
    }
}
