<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Site;

use App\HttpTenantApi\Resources\TenantSpecsResource;
use Domain\Site\Models\Site;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('tenant-specs', only: ['index'])]
class TenantSpecsController
{
    public function index(): TenantSpecsResource
    {
        $tenant = tenancy()?->tenant;
        
        if(! $tenant) {
            abort(404);
        }

        return TenantSpecsResource::make(
           $tenant
        );
    }
}
