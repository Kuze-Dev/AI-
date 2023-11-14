<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Site;

use App\HttpTenantApi\Resources\TenantSpecsResource;
use Spatie\RouteAttributes\Attributes\ApiResource;

#[ApiResource('tenant-specs', only: ['index'])]
class TenantSpecsController
{
    public function index(): TenantSpecsResource
    {
        $tenant = tenancy()->tenant;

        if (! $tenant) {
            abort(404);
        }

        return TenantSpecsResource::make(
            $tenant
        );
    }
}
