<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Order;

use App\HttpTenantApi\Resources\OrderResource;
use Domain\Order\Actions\PlaceOrderAction;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\Enums\PlaceOrderResult;
use Domain\Order\Models\Order;
use Domain\Order\Requests\PlaceOrderRequest;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('orders', apiResource: true),
    Middleware(['auth:sanctum'])
]
class OrderController
{
    public function index()
    {
        return OrderResource::collection(
            QueryBuilder::for(
                Order::whereBelongsTo(auth()->user())
            )
                ->allowedFilters(['status'])
                ->allowedSorts(['reference', 'total', 'status', 'created_at'])
                ->jsonPaginate()
        );
    }

    public function store(PlaceOrderRequest $request)
    {
        $validatedData = $request->validated();

        $result = app(PlaceOrderAction::class)
            ->execute(PlaceOrderData::fromArray($validatedData));

        if (PlaceOrderResult::SUCCESS != $result) {
            return response()->json([
                'message' => 'Order failed to be created'
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Order placed successfully',
            ]);
    }
}
