<?php

declare(strict_types=1);

namespace App\Features\CMS;

use Domain\Tenant\Models\Tenant;

class GoogleMapField
{
    public string $name = 'cms.googlemap';

    public string $label = 'Google Map Field';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
