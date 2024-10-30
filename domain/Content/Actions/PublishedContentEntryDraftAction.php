<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Internationalization\Actions\HandleUpdateDataTranslation;
use Domain\Internationalization\Models\Locale;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\MetaData\Actions\UpdateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class PublishedContentEntryDraftAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected DeleteContentEntryAction $deleteContentEntry,
    ) {
    }

    /** Execute create content entry query. */
    public function execute(ContentEntry $contentEntry, ContentEntry $draft_content_entry, ContentEntryData $contentEntryData): ContentEntry
    {
        $contentEntry->update([
            'author_id' => $contentEntryData->author_id,
            'title' => $contentEntryData->title,
            'published_at' => $contentEntryData->published_at,
            'data' => $contentEntryData->data,
            'status' => $contentEntryData->status,
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

        if (
            tenancy()->tenant?->features()->active(\App\Features\CMS\Internationalization::class) &&
            is_null($contentEntry->draftable_id)
        ) {

            app(HandleUpdateDataTranslation::class)->execute($contentEntry, $contentEntryData);

        }

        $this->deleteContentEntry->execute($draft_content_entry);

        return $contentEntry;
    }
}
