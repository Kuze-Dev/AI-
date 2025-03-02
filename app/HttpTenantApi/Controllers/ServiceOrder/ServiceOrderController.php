<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use Illuminate\Container\Attributes\CurrentUser;
use App\HttpTenantApi\Resources\ServiceOrderResource;
use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Actions\CreateServiceOrderAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Exceptions\InvalidServiceBillException;
use Domain\ServiceOrder\Exceptions\ServiceStatusMustBeActive;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Requests\ServiceOrderStoreRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use Symfony\Component\HttpFoundation\Response;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('service-order', except: ['destroy']),
    Middleware(['auth:sanctum'])
]
class ServiceOrderController
{
    public function index(#[CurrentUser] Customer $customer): JsonApiResourceCollection
    {

        return ServiceOrderResource::collection(
            QueryBuilder::for(ServiceOrder::query()->whereBelongsTo($customer))
                ->defaultSort('-created_at')
                ->allowedFilters(['status', 'reference'])
                ->allowedIncludes(['serviceBills', 'service.media'])
                ->allowedSorts(['reference', 'total_price', 'status', 'created_at'])
                ->jsonPaginate()
        );
    }

    public function show(string $serviceOrder,#[CurrentUser] Customer $customer): ServiceOrderResource
    {

        return ServiceOrderResource::make(
            QueryBuilder::for(ServiceOrder::whereReference($serviceOrder)->whereBelongsTo($customer))
                ->allowedIncludes(['serviceBills', 'service.media'])
                ->firstOrFail()
        );
    }

    public function store(ServiceOrderStoreRequest $request): mixed
    {
        try {

            $validatedData = $request->validated();

            return DB::transaction(
                fn () => ServiceOrderResource::make(
                    app(CreateServiceOrderAction::class)->execute(
                        new ServiceOrderData(
                            customer_id: (int) Auth::id(),
                            service_id: (int) $validatedData['service_id'],
                            schedule: now()->parse($validatedData['schedule']),
                            service_address_id: $validatedData['service_address_id'],
                            billing_address_id: $validatedData['billing_address_id'],
                            is_same_as_billing: $validatedData['is_same_as_billing'],
                            additional_charges: $validatedData['additional_charges'],
                            form: $validatedData['form']
                        )
                    )
                )
            );

        } catch (ServiceStatusMustBeActive) {
            return response(
                ['message' => trans('Service is currently unavailable')],
                Response::HTTP_NOT_FOUND
            );
        } catch (InvalidServiceBillException) {
            return response(
                ['message' => trans('Service Bill not found')],
                Response::HTTP_NOT_FOUND
            );
        } catch (Exception $e) {
            report($e);

            return response(
                ['message' => trans('Something went wrong!')],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
