<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use App\HttpTenantApi\Resources\ServiceOrderResource;
use Domain\ServiceOrder\Actions\ChangeServiceOrderStatusAction;
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
use Exception;

#[
    ApiResource('service-order', except: ['destroy']),
    Middleware(['auth:sanctum'])
]
class ServiceOrderController
{
    public function index(): JsonApiResourceCollection
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        return ServiceOrderResource::collection(
            QueryBuilder::for(ServiceOrder::query()->whereBelongsTo($customer))
                ->defaultSort('-created_at')
                ->allowedFilters(['status', 'reference'])
                ->allowedIncludes(['serviceBills'])
                ->allowedSorts(['reference', 'total_price', 'status', 'created_at'])
                ->jsonPaginate()
        );
    }

    public function show(string $serviceOrder): ServiceOrderResource
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        return ServiceOrderResource::make(
            QueryBuilder::for(ServiceOrder::whereReference($serviceOrder)->whereBelongsTo($customer))
                ->allowedIncludes(['serviceBills'])
                ->firstOrFail()
        );
    }

    public function store(ServiceOrderStoreRequest $request, PlaceServiceOrderAction $placeServiceOrderAction): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            /** @var \Domain\Customer\Models\Customer $customer */
            $customer = auth()->user();

            $serviceBill = $placeServiceOrderAction->execute($validatedData, $customer->id, null);

            if ( ! $serviceBill instanceof ServiceBill) {
                throw new InvalidServiceBillException();
            }

            app(ChangeServiceOrderStatusAction::class)->execute($serviceBill->serviceOrder);

            return response()->json([
                'message' => 'Service order placed successfully',
                'data' => $serviceBill,
            ], 201);
        } catch (InvalidServiceBillException) {
            return response()->json([
                'message' => 'Invalid Service Bill',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
            ], 404);
        }
    }
}
