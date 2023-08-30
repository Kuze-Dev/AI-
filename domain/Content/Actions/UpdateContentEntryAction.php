<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Models\ContentEntry;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\MetaData\Actions\UpdateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;
use Domain\Internationalization\Models\Locale;

class UpdateContentEntryAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
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

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)) {

            $contentEntry->sites()->sync($contentEntryData->sites);
        }

        return $contentEntry;
    }
}
