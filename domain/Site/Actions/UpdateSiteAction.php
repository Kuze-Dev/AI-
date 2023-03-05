<?php

declare(strict_types=1);

namespace Domain\Site\Actions;

use Domain\Site\DataTransferObjects\SiteData;
use Domain\Site\Models\Site;

class UpdateSiteAction
{
    public function execute(Site $site, SiteData $siteData): Site
    {
        $site->update([
            'name' => $siteData->name,
        ]);

        return $site;
    }
}
