<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Auth\Customer;

use App\Features\Customer\CustomerBase;
use App\HttpTenantApi\Resources\TierResource;
use Domain\Tier\Models\Tier;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('tiers', only: ['index']),
    Middleware('feature.tenant:' . CustomerBase::class)
]
class TierController
{
    public function index(): JsonApiResourceCollection
    {
        return TierResource::collection(
            QueryBuilder::for(Tier::query())
                ->jsonPaginate()
        );
    }
}
