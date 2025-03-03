<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Order\PrivateOrder;

use Illuminate\Container\Attributes\CurrentUser;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\OrderResource;
use Domain\Customer\Models\Customer;
use Domain\Order\Actions\PlaceOrderAction;
use Domain\Order\Actions\UpdateOrderAction;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\UpdateOrderData;
use Domain\Order\Exceptions\OrderEmailSettingsException;
use Domain\Order\Exceptions\OrderEmailSiteSettingsException;
use Domain\Order\Models\Order;
use Domain\Order\Notifications\OrderFailedNotifyAdmin;
use Domain\Order\Requests\PlaceOrderRequest;
use Domain\Order\Requests\UpdateOrderRequest;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\Exceptions\PaymentException;
use Domain\Shipment\API\AusPost\Exceptions\AusPostServiceNotFoundException;
use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mailer\Exception\TransportException;

#[
    Resource('orders', apiResource: true, except: 'destroy', names: 'customer.orders'),
    Middleware(['auth:sanctum'])
]
class OrderController extends Controller
{
    public function index(#[CurrentUser('sanctum')] Customer $customer): mixed
    {

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

    public function store(PlaceOrderRequest $request): mixed
    {
        $validatedData = $request->validated();

        try {
            $result = DB::transaction(function () use ($validatedData) {
                $order = app(PlaceOrderAction::class)
                    ->execute(PlaceOrderData::fromArray($validatedData));

                if (is_array($order) && $order['order'] instanceof Order) {

                    return [
                        'message' => 'Order placed successfully',
                        'data' => $order,
                    ];
                }
            });

            return response()->json($result);
        } catch (TransportException) {
            return response()->json([
                'mail' => 'Something wrong with mailer',
            ], 404);
        } catch (USPSServiceNotFoundException|AusPostServiceNotFoundException) {
            return response()->json([
                'service_id' => 'Shipping method service id is required',
            ], 404);
        } catch (PaymentException) {
            app(OrderFailedNotifyAdmin::class)->execute('This error is occurring due to an issue with the payment credentials on your website.
            Please ensure that your payment settings are configured correctly.', 'ecommerceSettings.payments');

            return response()->json([
                'payment' => 'Invalid Payment Credentials',
            ], 404);
        } catch (OrderEmailSettingsException $e) {
            app(OrderFailedNotifyAdmin::class)->execute('This error is occurring due to an issue with the email sender on your website.
            Please ensure that your order settings are configured correctly.', 'ecommerceSettings.order');

            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        } catch (OrderEmailSiteSettingsException $e) {
            app(OrderFailedNotifyAdmin::class)->execute('This error is occurring due to an issue with the logo on your website.
            Please ensure that your site settings are configured correctly.', 'cmsSettings.site');

            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        } catch (BadRequestHttpException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            Log::error([
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    public function show(Order $order,#[CurrentUser('sanctum')] Customer $customer): OrderResource
    {

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
