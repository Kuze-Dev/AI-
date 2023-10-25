<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Country;

use App\Features\ECommerce\ECommerceBase;
use App\HttpTenantApi\Resources\CountryResource;
use Domain\Address\Models\Country;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('countries', only: ['index', 'show']),
    Middleware('feature.tenant:'.ECommerceBase::class)
]
class CountryController
{
    public function index(): JsonApiResourceCollection
    {
        return CountryResource::collection(
            QueryBuilder::for(Country::whereActive(true))
                ->allowedIncludes([
                    'states',
                ])
                ->allowedFilters(['name', 'active', 'code'])
                ->jsonPaginate()
        );
    }

    public function show(string $country): CountryResource
    {
        return CountryResource::make(
            QueryBuilder::for(Country::whereCode($country)->whereActive(true))
                ->allowedIncludes([
                    'states',
                ])
                ->firstOrFail()
        );
    }
}
