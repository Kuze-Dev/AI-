<?php

declare(strict_types=1);

namespace App\Features\CMS;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class OpenAI implements FeatureContract
{
    public string $name = 'cms.open-ai';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        return trans('Open AI');
    }
}
