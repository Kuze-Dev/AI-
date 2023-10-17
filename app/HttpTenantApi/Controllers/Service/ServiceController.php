<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Service;

use App\Features\Service\ServiceBase;
use App\HttpTenantApi\Resources\ServiceResource;
use Domain\Service\Models\Service;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('services', only: ['index', 'show']),
    Middleware('feature.tenant:' . ServiceBase::class)
]
class ServiceController
{
    public function index(): JsonApiResourceCollection
    {
        return ServiceResource::collection(
            QueryBuilder::for(Service::query()->whereStatus(true))
                ->allowedFilters([
                    'name',
                    'selling_price',
                    'retail_price',
                    'is_subscription',
                    'is_special_offer',
                    'is_featured',
                    'pay_upfront',
                    'status',
                    'needs_approval',
                    AllowedInclude::relationship('taxonomyTerms'),
                ])
                ->allowedIncludes([
                    'taxonomyTerms',
                    'media',
                    'metaData',
                ])
                ->jsonPaginate()
        );
    }

    public function show(string $service): ServiceResource
    {
        return ServiceResource::make(
            QueryBuilder::for(Service::whereUuid($service)->whereStatus(true))
                ->allowedIncludes([
                    'taxonomyTerms',
                    'media',
                    'metaData',
                ])->firstOrFail()
        );
    }
}
