<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use App\HttpTenantApi\Resources\ServiceBillResource;
use Domain\ServiceOrder\Models\ServiceBill;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('service-order/service-bills', only: ['show', 'update']),
    Middleware(['auth:sanctum'])
]
class ServiceBillController
{
    public function show(string $serviceOrderRef): JsonApiResourceCollection
    {
        return ServiceBillResource::collection(
            QueryBuilder::for(ServiceBill::query()->whereServiceOrderRef($serviceOrderRef))
                ->defaultSort('-created_at')
                ->allowedIncludes(['serviceOrder'])
                ->allowedFilters(['status', 'reference'])
                ->allowedSorts(['reference', 'total_amount', 'status', 'created_at', 'due_date', 'bill_date'])
                ->jsonPaginate()
        );
    }
}
