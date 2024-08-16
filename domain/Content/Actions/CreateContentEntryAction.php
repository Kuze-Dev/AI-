<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Internationalization\Models\Locale;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class CreateContentEntryAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected CreateBlueprintDataAction $createBlueprintDataAction,
    ) {
    }

    /** Execute create content entry query. */
    public function execute(Content $content, ContentEntryData $contentEntryData): ContentEntry
    {
        /** @var ContentEntry $contentEntry */
        $contentEntry = $content->contentEntries()
            ->create([
                'title' => $contentEntryData->title,
                'data' => $contentEntryData->data,
                'published_at' => $contentEntryData->published_at,
                'author_id' => $contentEntryData->author_id,
                'locale' => $contentEntryData->locale ?? Locale::where('is_default', true)->first()?->code,
                'order' => $content->is_sortable ? $content->contentEntries->count() + 1 : null,
                'status' => $contentEntryData->status,
            ]);

        $this->createMetaData->execute($contentEntry, $contentEntryData->meta_data);

        $contentEntry->taxonomyTerms()
            ->attach($contentEntryData->taxonomy_terms);

        $this->createOrUpdateRouteUrl->execute($contentEntry, $contentEntryData->route_url_data);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)) {

            $contentEntry->sites()->sync($contentEntryData->sites);
        }

        $this->createBlueprintDataAction->execute($contentEntry);

        return $contentEntry;
    }
}
