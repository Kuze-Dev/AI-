<?php

declare(strict_types=1);

namespace Domain\Tenant;

use App\Features\FeatureContract;

final class TenantFeatureSupport
{
    private function __construct() {}

    /**
     * @param  class-string<FeatureContract>  $feature
     */
    public static function active(string $feature): bool
    {
        return TenantSupport::model()->features()->active($feature);
    }

    /**
     * @param  class-string<FeatureContract>  $feature
     */
    public static function inactive(string $feature): bool
    {
        return TenantSupport::model()->features()->inactive($feature);
    }

    /**
     * @param  array<int, class-string<FeatureContract>>  $features
     */
    public static function allAreActive(array $features): bool
    {
        return TenantSupport::model()->features()->allAreActive($features);
    }

    /**
     * @param  array<int, class-string<FeatureContract>>  $features
     */
    public static function someAreActive(array $features): bool
    {
        return TenantSupport::model()->features()->someAreActive($features);
    }

    /**
     * @param  array<int, class-string<FeatureContract>>  $features
     */
    public static function someAreInactive(array $features): bool
    {
        return TenantSupport::model()->features()->someAreInactive($features);
    }
}
