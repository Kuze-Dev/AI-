<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use Domain\Content\DataTransferObjects\ContentData;
use Domain\Content\Models\Content;
use Illuminate\Support\Facades\Auth;

class UpdateContentAction
{
    /**
     * Execute operations for updating
     * content and save content query.
     */
    public function execute(Content $content, ContentData $contentData): Content
    {
        $content->update([
            'name' => $contentData->name,
            'prefix' => $contentData->prefix,
            'past_publish_date_behavior' => $contentData->past_publish_date_behavior,
            'future_publish_date_behavior' => $contentData->future_publish_date_behavior,
            'is_sortable' => $contentData->is_sortable,
        ]);

        $content->taxonomies()
            ->sync($contentData->taxonomies);

        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) &&
            Auth::user()?->hasRole(config('domain.role.super_admin'))
        ) {

            $content->sites()
                ->sync($contentData->sites);

        }

        return $content;
    }
}
