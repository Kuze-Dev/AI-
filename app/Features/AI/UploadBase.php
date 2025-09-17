<?php

declare(strict_types=1);

namespace App\Features\AI;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class UploadBase implements FeatureContract
{
    public string $name = 'ai.upload';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        return trans('Upload');
    }
}
