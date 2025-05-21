<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use Domain\Blueprint\Actions\CreateBlueprintDataAction;
use Domain\Blueprint\Actions\UpdateBlueprintDataAction;
use Domain\Content\DataTransferObjects\ContentEntryData;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Internationalization\Actions\HandleDataTranslation;
use Domain\Internationalization\Models\Locale;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;

class CreateContentEntryTranslationAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected CreateBlueprintDataAction $createBlueprintDataAction,
        protected UpdateBlueprintDataAction $updateBlueprintDataAction,
    ) {}

    /** Execute create content entry query. */
    public function execute(ContentEntry $content, ContentEntryData $contentEntryData): ContentEntry
    {
        /** @var ContentEntry $contentEntryTranslation */
        $contentEntryTranslation = $content->dataTranslation()
            ->create([
                'title' => $contentEntryData->title,
                'data' => $contentEntryData->data,
                'content_id' => $content->content_id,
                'published_at' => $contentEntryData->published_at,
                'author_id' => $contentEntryData->author_id,
                'locale' => $contentEntryData->locale ?? Locale::where('is_default', true)->first()?->code,
                'status' => $contentEntryData->status,
            ]);

        $this->createBlueprintDataAction->execute($contentEntryTranslation);

        $this->createMetaData->execute($contentEntryTranslation, $contentEntryData->meta_data);

        $contentEntryTranslation->taxonomyTerms()
            ->attach($contentEntryData->taxonomy_terms);

        $this->createOrUpdateRouteUrl->execute($contentEntryTranslation, $contentEntryData->route_url_data);

        if (\Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)) {

            $contentEntryTranslation->sites()->sync($contentEntryData->sites);
        }

        app(HandleDataTranslation::class)->execute($content, $contentEntryTranslation);

        $contentEntryTranslation->refresh();

        return $contentEntryTranslation;

    }
}
