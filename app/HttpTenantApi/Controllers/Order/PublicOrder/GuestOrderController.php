<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Order\PublicOrder;

use App\Features\ECommerce\AllowGuestOrder;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\GuestOrderResource;
use Domain\Order\Actions\PublicOrder\GuestPlaceOrderAction;
use Domain\Order\Actions\PublicOrder\GuestUpdateOrderAction;
use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Domain\Order\DataTransferObjects\UpdateOrderData;
use Domain\Order\Exceptions\OrderEmailSettingsException;
use Domain\Order\Exceptions\OrderEmailSiteSettingsException;
use Domain\Order\Models\Order;
use Domain\Order\Notifications\OrderFailedNotifyAdmin;
use Domain\Order\Requests\PublicOrder\GuestPlaceOrderRequest;
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
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mailer\Exception\TransportException;

#[
    ApiResource('guest/orders', only: ['store', 'show', 'update'], names: 'guest.orders'),
    Middleware(['feature.tenant:'.AllowGuestOrder::class])
]
class GuestOrderController extends Controller
{
    public function store(GuestPlaceOrderRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $result = DB::transaction(function () use ($validatedData) {
                $order = app(GuestPlaceOrderAction::class)
                    ->execute(GuestPlaceOrderData::fromArray($validatedData));

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

    public function show(Order $order): GuestOrderResource
    {
        $model = QueryBuilder::for(
            $order->with([
                'shippingAddress',
                'billingAddress',
                'orderLines.media',
                'orderLines.review.media',
                'payments.paymentMethod.media',
            ])->whereReference($order->reference)
        )
            ->allowedIncludes(['orderLines', 'payments.media', 'payments.paymentMethod.media', 'shippingMethod'])->first();

        return GuestOrderResource::make($model);
    }

    public function update(UpdateOrderRequest $request, Order $order): mixed
    {
        $validatedData = $request->validated();

        try {
            $dbResult = DB::transaction(function () use ($validatedData, $order) {
                $result = app(GuestUpdateOrderAction::class)
                    ->execute($order, UpdateOrderData::fromArray($validatedData));

                if ($result instanceof PaymentAuthorize) {
                    return $result;
                }

                return [
                    'message' => 'Order updated successfully',
                ];
            });

            return response()->json($dbResult);
        } catch (BadRequestHttpException $e) {
            return response()->json([
                'message' => 'Order failed to be updated',
                'error' => $e->getMessage(),
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
}
