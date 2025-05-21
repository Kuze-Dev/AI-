<?php

declare(strict_types=1);

namespace App\Features\CMS;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class GoogleMapField implements FeatureContract
{
    public string $name = 'cms.googlemap';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        return trans('Google Map Field');
    }
}
