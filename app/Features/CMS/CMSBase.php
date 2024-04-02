<?php

declare(strict_types=1);

namespace App\Features\CMS;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class CMSBase implements FeatureContract
{
    public string $name = 'cms.base';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        return trans('CMS');
    }
}
