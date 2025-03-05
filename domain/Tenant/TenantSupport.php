<?php

declare(strict_types=1);

namespace Domain\Tenant;

use Domain\Tenant\Models\Tenant;

final class TenantSupport
{
    private function __construct()
    {
    }

    public static function initialized(): bool
    {
        return tenancy()->initialized;
    }

    public static function model(): Tenant
    {
        /** @phpstan-ignore return.type */
        return tenancy()->tenant;
    }
//
//    public static function modelNullable(): ?Tenant
//    {
//        if (self::initialized()) {
//            return self::model();
//        }
//
//        return null;
//    }
}
