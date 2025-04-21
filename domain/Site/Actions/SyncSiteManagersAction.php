<?php

declare(strict_types=1);

namespace Domain\Site\Actions;

use App\Features\CMS\SitesManagement;
use Domain\Site\DataTransferObjects\SiteData;
use Domain\Site\Models\Site;
use Domain\Tenant\TenantFeatureSupport;

class SyncSiteManagersAction
{
    public function execute(Site $model, SiteData $siteData): void
    {
        if (TenantFeatureSupport::active(SitesManagement::class)) {
            $model->siteManager()->sync($siteData->site_manager ?? []);
        }
    }
}
