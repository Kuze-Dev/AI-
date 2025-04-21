<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use App\Features\CMS\SitesManagement;
use Domain\Content\DataTransferObjects\ContentData;
use Domain\Content\Models\Content;
use Domain\Tenant\TenantFeatureSupport;

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
            'visibility' => $contentData->visibility,
            'past_publish_date_behavior' => $contentData->past_publish_date_behavior,
            'future_publish_date_behavior' => $contentData->future_publish_date_behavior,
            'is_sortable' => $contentData->is_sortable,
        ]);

        $content->taxonomies()
            ->sync($contentData->taxonomies);

        if (TenantFeatureSupport::active(SitesManagement::class) &&
            filament_admin()->hasRole(config('domain.role.super_admin'))
        ) {

            $content->sites()
                ->sync($contentData->sites);

        }

        return $content;
    }
}
