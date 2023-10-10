<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use App\Features\Service\ServiceBase;
use App\HttpTenantApi\Resources\ServiceOrderResource;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Models\ServiceOrder;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('service-order', except: ['delete']),
    Middleware('feature.tenant:' . ServiceBase::class)
]
class ServiceOrderController
{
    public function index(): JsonApiResourceCollection
    {
        return ServiceOrderResource::collection(
            QueryBuilder::for(ServiceOrder::query()->latest())
                ->jsonPaginate()
        );
    }

    public function show(string $serviceOrder): ServiceOrderResource
    {
        return ServiceOrderResource::make(
            QueryBuilder::for(ServiceOrder::whereReference($serviceOrder))->firstOrFail()
        );
    }


    public function store(string $service)
    {

    }
}
