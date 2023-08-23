<?php

declare(strict_types=1);

namespace App\Features\CMS;

use Domain\Tenant\Models\Tenant;

class SitesManagement
{
    public string $name = 'cms.sites-management';

    public string $label = 'Sites Management';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
