<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Site;

use App\HttpTenantApi\Resources\TenantSpecsResource;
use Domain\Tenant\TenantSupport;
use Spatie\RouteAttributes\Attributes\ApiResource;

#[ApiResource('tenant-specs', only: ['index'])]
class TenantSpecsController
{
    public function index(): TenantSpecsResource
    {
        return TenantSpecsResource::make(
            TenantSupport::model()
        );
    }
}
