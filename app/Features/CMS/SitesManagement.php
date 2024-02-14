<?php

declare(strict_types=1);

namespace App\Features\CMS;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class SitesManagement implements FeatureContract
{
    public string $name = 'cms.sites-management';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    public function getLabel(): string
    {
        return trans('Sites Management');
    }
}
