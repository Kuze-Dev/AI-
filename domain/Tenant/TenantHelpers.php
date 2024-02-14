<?php

declare(strict_types=1);

namespace Domain\Tenant;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

final class TenantHelpers
{
    private function __construct()
    {
    }

    /**
     * @param  class-string<FeatureContract>|array<int, class-string<FeatureContract>>  $feature
     */
    public static function isFeatureActive(string|array $feature): bool
    {
        /** @var Tenant $tenant */
        $tenant = tenancy()->tenant;

        return $tenant->features()->allAreActive($feature);
    }
}
