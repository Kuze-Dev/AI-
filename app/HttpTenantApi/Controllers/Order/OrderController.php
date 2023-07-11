<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Order;

use Domain\Order\Actions\PlaceOrderAction;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\Enums\PlaceOrderResult;
use Domain\Order\Requests\PlaceOrderRequest;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('orders', apiResource: true),
    Middleware(['auth:sanctum'])
]
class OrderController
{
    public function store(PlaceOrderRequest $request)
    {
        $validatedData = $request->validated();

        $result = app(PlaceOrderAction::class)
            ->execute(PlaceOrderData::fromArray($validatedData));

        // return $result;

        if (PlaceOrderResult::SUCCESS != $result) {
            return response()->json([
                'message' => 'Order failed to be created'
                // 'message' => $result
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Order placed successfully',
            ]);
    }
}
