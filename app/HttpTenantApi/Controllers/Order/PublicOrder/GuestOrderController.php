<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Order\PublicOrder;

use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\OrderResource;
use Domain\Order\Actions\PublicOrder\GuestPlaceOrderAction;
use Domain\Order\Actions\UpdateOrderAction;
use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Domain\Order\DataTransferObjects\UpdateOrderData;
use Domain\Order\Models\Order;
use Domain\Order\Requests\PublicOrder\GuestPlaceOrderRequest;
use Domain\Order\Requests\UpdateOrderRequest;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\Exceptions\PaymentException;
use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\Exception\TransportException;

#[
    Resource('guest/orders', apiResource: true, except: 'destroy'),
]
class GuestOrderController extends Controller
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

    public function store(GuestPlaceOrderRequest $request): JsonResponse
    {
        $sessionId = $request->bearerToken();

        if (is_null($sessionId)) {
            abort(403);
        }

        $validatedData = $request->validated();

        try {
            $result = app(GuestPlaceOrderAction::class)
                ->execute(GuestPlaceOrderData::fromArray($validatedData));

            if ($result instanceof TransportException) {
                return response()->json([
                    'mail' => 'Something wrong with mailer',
                ], 404);
            }

            if ($result instanceof USPSServiceNotFoundException) {
                return response()->json([
                    'service_id' => 'Shipping method service id is required',
                ], 404);
            }

            if ($result instanceof PaymentException) {
                return response()->json([
                    'payment' => 'Invalid Payment Credentials',
                ], 404);
            }

            if ($result instanceof HttpException) {
                return response()->json([
                    'message' => $result->getMessage(),
                ], 422);
            }

            if (is_array($result) && $result['order'] instanceof Order) {
                return response()
                    ->json([
                        'message' => 'Order placed successfully',
                        'data' => $result,
                    ]);
            }

            return response()->json([
                'message' => 'Order failed to be created',
            ], 400);
        } catch (Exception $e) {
            Log::info('OrderController exception ' . $e);

            return response()->json([
                'message' => 'Something went wrong',
            ], 400);
        }
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
