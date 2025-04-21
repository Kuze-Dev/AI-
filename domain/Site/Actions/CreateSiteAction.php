<?php

declare(strict_types=1);

namespace Domain\Site\Actions;

use Domain\Site\DataTransferObjects\SiteData;
use Domain\Site\Models\Site;

class CreateSiteAction
{
    public function __construct(
        protected SyncSiteManagersAction $syncSiteManagers,
    ) {}

    public function execute(SiteData $siteData): Site
    {
        $model = Site::create([
            'name' => $siteData->name,
            'domain' => $siteData->domain,
            'deploy_hook' => $siteData->deploy_hook,
        ]);

        $this->syncSiteManagers->execute($model, $siteData);

        return $model;
    }
}
