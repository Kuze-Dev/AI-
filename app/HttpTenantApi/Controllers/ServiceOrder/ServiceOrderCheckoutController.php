<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use Domain\ServiceOrder\Actions\CheckoutServiceOrderAction;
use Domain\ServiceOrder\Exceptions\InvalidServiceTransactionException;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Domain\ServiceOrder\Requests\ServiceTransactionStoreRequest;
use Illuminate\Http\JsonResponse;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;

#[
    ApiResource('service-transaction', only: ['store']),
    Middleware(['auth:sanctum'])
]
class ServiceOrderCheckoutController
{
    public function store(ServiceTransactionStoreRequest $request, CheckoutServiceOrderAction $checkoutServiceOrderAction): JsonResponse
    {
        $validatedData = $request->validated();

        $serviceTransaction = $checkoutServiceOrderAction->execute($validatedData);

        if ( ! $serviceTransaction instanceof ServiceTransaction) {
            throw new InvalidServiceTransactionException();
        }

        return response()->json(
            [
                'message' => 'Service Transaction created successfully',
                'data' => $serviceTransaction,
            ],
            201
        );
    }
}
