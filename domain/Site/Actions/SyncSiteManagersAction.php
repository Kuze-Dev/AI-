<?php

declare(strict_types=1);

namespace Domain\Site\Actions;

use Domain\Site\DataTransferObjects\SiteData;
use Domain\Site\Models\Site;

class SyncSiteManagersAction
{
    public function execute(Site $model, SiteData $siteData): void
    {
        if (tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class)) {
            $model->siteManager()->sync($siteData->site_manager ?? []);
        }
    }
}
