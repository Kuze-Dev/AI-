<?php

declare(strict_types=1);

namespace App\Features\CMS;

use Domain\Tenant\Models\Tenant;

class Internationalization
{
    public string $name = 'cms.i18n';

    public string $label = 'Internationalization';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
