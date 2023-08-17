<?php

declare(strict_types=1);

namespace Domain\Site\Actions;

use Domain\Site\DataTransferObjects\SiteData;
use Domain\Site\Models\Site;

class CreateSiteAction
{
    public function execute(SiteData $siteData): Site
    {
        return Site::create([
            'name' => $siteData->name,
        ]);
    }
}
