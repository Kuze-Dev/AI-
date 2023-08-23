<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Order;

use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\OrderResource;
use Domain\Order\Actions\PlaceOrderAction;
use Domain\Order\Actions\UpdateOrderAction;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\UpdateOrderData;
use Domain\Order\Models\Order;
use Domain\Order\Requests\PlaceOrderRequest;
use Domain\Order\Requests\UpdateOrderRequest;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[
    Resource('orders', apiResource: true, except: 'destroy'),
    Middleware(['auth:sanctum'])
]
class OrderController extends Controller
{
    public function index(): mixed
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        return OrderResource::collection(
            QueryBuilder::for(Order::with([
                'shippingAddress',
                'billingAddress',
                'orderLines.media',
            ])->whereBelongsTo($customer))
                ->defaultSort('-created_at')
                ->allowedIncludes(['orderLines', 'orderLines.review.media'])
                ->allowedFilters(['status', 'reference'])
                ->allowedSorts(['reference', 'total', 'status', 'created_at'])
                ->jsonPaginate()
        );
    }

    public function store(PlaceOrderRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $result = app(PlaceOrderAction::class)
            ->execute(PlaceOrderData::fromArray($validatedData));

        if ($result instanceof USPSServiceNotFoundException) {
            return response()->json([
                'service_id' => 'Shipping method service id is required',
            ], 404);
        }

        if ($result instanceof HttpException) {
            return response()->json([
                'message' => $result->getMessage(),
            ], 422);
        }

        /** @phpstan-ignore-next-line */
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

    public function show(Order $order): OrderResource
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        $model = QueryBuilder::for(
            $order->with([
                'shippingAddress',
                'billingAddress',
                'orderLines.media',
                'orderLines.review.media',
                'payments.paymentMethod.media',
            ])->whereBelongsTo($customer)
                ->whereReference($order->reference)
        )
            ->allowedIncludes(['orderLines', 'payments.media', 'payments.paymentMethod.media', 'shippingMethod'])->first();

        return OrderResource::make($model);
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $validatedData = $request->validated();

        $result = app(UpdateOrderAction::class)
            ->execute($order, UpdateOrderData::fromArray($validatedData));

        if (is_string($result)) {
            return response()->json([
                'message' => 'Order failed to be updated',
                'error' => $result,
            ], 400);
        }

        if ($result instanceof PaymentAuthorize) {
            return response()->json($result);
        }

        return response()
            ->json([
                'message' => 'Order updated successfully',
            ]);
    }
}
