<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Order\DataTransferObjects\UpdateOrderData;
use Domain\Order\Events\OrderStatusUpdatedEvent;
use Domain\Order\Models\Order;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Log;
use Throwable;
use Illuminate\Support\Facades\DB;

class UpdateOrderAction
{
    public function execute(
        Order $order,
        UpdateOrderData $updateOrderData
    ): Order|string|PaymentAuthorize|BadRequestHttpException {
        return DB::transaction(function () use ($order, $updateOrderData) {
            try {
                DB::beginTransaction();

                /** @var \Domain\Order\Models\Order $orderWithPayment */
                $orderWithPayment = $order->load('payments');

                if ($updateOrderData->status) {
                    $order = app(UpdateOrderPaymentAction::class)->status(
                        $orderWithPayment,
                        $updateOrderData->status,
                        $updateOrderData->notes
                    );

                    /** @var \Domain\Customer\Models\Customer $customer */
                    $customer = auth()->user();

                    event(new OrderStatusUpdatedEvent(
                        $customer,
                        $orderWithPayment,
                        $updateOrderData->status
                    ));
                }

                if (
                    $updateOrderData->type == 'bank-transfer' &&
                    $updateOrderData->proof_of_payment !== null
                ) {
                    try {
                        app(UpdateOrderPaymentAction::class)->bankTransfer(
                            $orderWithPayment,
                            $updateOrderData->proof_of_payment,
                            $updateOrderData->notes
                        );
                    } catch (Throwable $e) {
                        return $e->getMessage();
                    }
                } else {
                    if ($updateOrderData->type != 'status') {
                        try {
                            return app(UpdateOrderPaymentAction::class)->withGateway(
                                $orderWithPayment,
                                $updateOrderData->type
                            );
                        } catch (Throwable $e) {
                            return $e->getMessage();
                        }
                    }
                }

                DB::commit();

                return $order;
            } catch (Exception $e) {
                DB::rollBack();
                // Log::info($e);
                return 'Something went wrong';
            }
        });
    }
}
