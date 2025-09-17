<?php

declare(strict_types=1);

namespace App\Features\AI;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class OpenAIBase implements FeatureContract
{
    public string $name = 'ai.base';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        return trans('Open AI Management');
    }
}
