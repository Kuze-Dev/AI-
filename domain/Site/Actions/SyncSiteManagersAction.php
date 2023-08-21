<?php

declare(strict_types=1);

namespace Domain\Site\Actions;

use Domain\Site\DataTransferObjects\SiteData;
use Domain\Site\Models\Site;

class SyncSiteManagersAction
{
    public function execute(Site $model, SiteData $siteData): void
    {

        $model->siteManager()->sync($siteData->site_manager);

    }
}
