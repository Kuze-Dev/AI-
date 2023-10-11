<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use App\HttpTenantApi\Resources\ServiceOrderResource;
use Domain\ServiceOrder\Actions\PlaceServiceOrderAction;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillException;
use Domain\ServiceOrder\Models\ServiceBill;
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

    public function store(ServiceOrderStoreRequest $request, PlaceServiceOrderAction $placeServiceOrderAction): JsonResponse
    {
        $validatedData = $request->validated();

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        $serviceBill = $placeServiceOrderAction->execute($validatedData, $customer->id, null);

        if ( ! $serviceBill instanceof ServiceBill) {
            throw new InvalidServiceBillException();
        }

        return response()->json([
            'message' => 'Service order placed successfully',
            'data' => $serviceBill,
        ], 201);
    }
}
