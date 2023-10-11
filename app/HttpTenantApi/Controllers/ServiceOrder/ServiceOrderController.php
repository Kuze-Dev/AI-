<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use App\HttpTenantApi\Resources\ServiceOrderResource;
use Domain\ServiceOrder\Actions\CreateServiceOrderAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Requests\ServiceOrderStoreRequest;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('service-order', except: ['destroy']),
    Middleware(['auth:sanctum'])
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

    public function store(ServiceOrderStoreRequest $request, CreateServiceOrderAction $createServiceOrderAction): JsonResponse
    {
        $validatedData = $request->validated();

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        $createServiceOrderAction->execute(ServiceOrderData::fromArray($validatedData, $customer->id), null);

        return response()->json(['message' => 'Service Order created successfully'], 201);
    }
}
