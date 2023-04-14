<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use Domain\Content\DataTransferObjects\ContentData;
use Domain\Content\Models\Content;

class CreateContentAction
{
    /** Execute create content query. */
    public function execute(ContentData $contentData): Content
    {
        $content = Content::create([
            'name' => $contentData->name,
            'slug' => $contentData->slug,
            'blueprint_id' => $contentData->blueprint_id,
            'past_publish_date_behavior' => $contentData->past_publish_date_behavior,
            'future_publish_date_behavior' => $contentData->future_publish_date_behavior,
            'is_sortable' => $contentData->is_sortable,
            'route_url' => $contentData->route_url,
        ]);

        $content->taxonomies()
            ->attach($contentData->taxonomies);

        return $content;
    }
}
