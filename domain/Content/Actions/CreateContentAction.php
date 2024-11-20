<?php

declare(strict_types=1);

namespace Domain\Content\Actions;

use App\Features\CMS\SitesManagement;
use Domain\Content\DataTransferObjects\ContentData;
use Domain\Content\Models\Content;
<<<<<<< HEAD
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Support\Facades\Auth;
=======
>>>>>>> develop

class CreateContentAction
{
    /** Execute create content query. */
    public function execute(ContentData $contentData): Content
    {
        $content = Content::create([
            'name' => $contentData->name,
            'prefix' => $contentData->prefix,
            'visibility' => $contentData->visibility,
            'blueprint_id' => $contentData->blueprint_id,
            'past_publish_date_behavior' => $contentData->past_publish_date_behavior,
            'future_publish_date_behavior' => $contentData->future_publish_date_behavior,
            'is_sortable' => $contentData->is_sortable,
        ]);

        $content->taxonomies()
            ->attach($contentData->taxonomies);

<<<<<<< HEAD
        if (TenantFeatureSupport::active(SitesManagement::class) &&
        Auth::user()?->hasRole(config('domain.role.super_admin'))
=======
        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)

>>>>>>> develop
        ) {

            $content->sites()
                ->sync($contentData->sites);

        }

        return $content;
    }
}
