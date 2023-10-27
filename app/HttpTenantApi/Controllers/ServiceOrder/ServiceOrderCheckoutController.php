<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\ServiceOrder;

use Domain\ServiceOrder\Actions\CheckoutServiceOrderAction;
use Domain\ServiceOrder\Exceptions\InvalidServiceTransactionException;
use Domain\ServiceOrder\Requests\ServiceTransactionStoreRequest;
use Exception;
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
        try {
            $validatedData = $request->validated();

            $data = $checkoutServiceOrderAction->execute($validatedData);

            if (! $data) {
                throw new InvalidServiceTransactionException();
            }

            return response()->json(
                [
                    'message' => 'Proceed to payment',
                    'data' => $data,
                ],
                201
            );
        } catch (InvalidServiceTransactionException) {
            return response()->json([
                'message' => 'Invalid Service Transaction',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
            ], 404);
        }
    }
}
