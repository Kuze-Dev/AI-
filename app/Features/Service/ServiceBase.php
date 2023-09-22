<?php

declare(strict_types=1);

namespace App\Features\Service;

use Domain\Tenant\Models\Tenant;

class ServiceBase
{
    public string $name = 'service.base';

    public string $label = 'Service Management';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
