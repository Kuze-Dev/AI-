<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use App\HttpTenantApi\Resources\ServiceOrderResource;
use Domain\ServiceOrder\Actions\ChangeServiceOrderStatusAction;
use Domain\ServiceOrder\Actions\PlaceServiceOrderAction;
use Domain\ServiceOrder\DataTransferObjects\PlaceServiceOrderData;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillException;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Requests\ServiceOrderStoreRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
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

    public function store(
        ServiceOrderStoreRequest $request,
        PlaceServiceOrderAction $placeServiceOrderAction
    ): JsonResponse {

        try {
            $validatedData = $request->validated();

            /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
            $serviceOrder = $placeServiceOrderAction->execute(
                new PlaceServiceOrderData(
                    customer_id: (int) Auth::id(),
                    service_id: (int) $validatedData->service_id,
                    schedule: $validatedData->schedule,
                    service_address_id: $validatedData->service_address_id,
                    billing_address_id: $validatedData->billing_address_id,
                    is_same_as_billing: $validatedData->is_same_as_billing,
                    additional_charges: $validatedData->additional_charges,
                    form: $validatedData->form
                )
            );

            return response()->json([
                'message' => 'Service order placed successfully',
                'data' => $serviceOrder->latestServiceBill(),
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
