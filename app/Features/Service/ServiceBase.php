<?php

declare(strict_types=1);

namespace App\Features\Service;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class ServiceBase implements FeatureContract
{
    public string $name = 'service.base';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    public function getLabel(): string
    {
        return trans('Service Management');
    }
}
