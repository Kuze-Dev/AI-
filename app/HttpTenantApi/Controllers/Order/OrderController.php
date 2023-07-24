<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Order;

use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\OrderResource;
use Domain\Order\Actions\PlaceOrderAction;
use Domain\Order\Actions\UpdateOrderAction;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\UpdateOrderData;
use Domain\Order\Enums\OrderResult;
use Domain\Order\Models\Order;
use Domain\Order\Requests\PlaceOrderRequest;
use Domain\Order\Requests\UpdateOrderRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('orders', apiResource: true, except: 'destroy'),
    Middleware(['auth:sanctum'])
]
class OrderController extends Controller
{
    public function index()
    {
        return OrderResource::collection(
            QueryBuilder::for(Order::with([
                'shippingAddress', 'billingAddress',
                'orderLines.media',
            ])->whereBelongsTo(auth()->user()))
                ->allowedIncludes(['orderLines'])
                ->allowedFilters(['status', 'reference', AllowedFilter::scope('for_payment')])
                ->allowedSorts(['reference', 'total', 'status', 'created_at'])
                ->jsonPaginate()
        );
    }

    public function store(PlaceOrderRequest $request)
    {
        $validatedData = $request->validated();

        $result = app(PlaceOrderAction::class)
            ->execute(PlaceOrderData::fromArray($validatedData));

        if ( ! $result['order'] instanceof Order) {
            return response()->json([
                'message' => 'Order failed to be created',
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Order placed successfully',
                'data' => $result,
            ]);
    }

    public function show(Order $order)
    {
        // return $order;
        // $this->authorize('view', $order);

        $model = QueryBuilder::for(
            $order->with([
                'shippingAddress', 'billingAddress',
                'orderLines.media', 'orderLines.review.media',
                'payments.paymentMethod.media',
            ])->whereBelongsTo(auth()->user())
                ->whereReference($order->reference)
        )
            ->allowedIncludes(['orderLines', 'payments.paymentMethod.media'])->first();

        return OrderResource::make($model);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        // $this->authorize('update', $order);

        $validatedData = $request->validated();

        $result = app(UpdateOrderAction::class)
            ->execute($order, UpdateOrderData::fromArray($validatedData));

        if (OrderResult::SUCCESS != $result) {
            return response()->json([
                'message' => 'Order failed to be updated',
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Order updated successfully',
            ]);
    }
}
