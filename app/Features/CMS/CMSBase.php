<?php

declare(strict_types=1);

namespace App\Features\CMS;

use Domain\Tenant\Models\Tenant;

class CMSBase
{
    public string $name = 'cms.base';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
