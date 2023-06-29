<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Country;
use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\CountryResource;
use Domain\Address\Models\Country;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('countries', only: ['index', 'show']),
    Middleware('feature.tenant:' . CMSBase::class)
]
class CountryController
{
    public function index(): JsonApiResourceCollection
    {
        return CountryResource::collection(
            QueryBuilder::for(Country::query())
                ->allowedIncludes([
                    'states',
                ])
                ->allowedFilters(['name', 'active', 'code'])
                ->jsonPaginate()
        );
    }

    public function show(string $content): CountryResource
    {
        return CountryResource::make(
            QueryBuilder::for(Country::whereSlug($content))
                ->allowedIncludes([
                    'states',
                ])
                ->firstOrFail()
        );
    }
}
