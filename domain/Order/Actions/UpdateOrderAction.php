<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Order\DataTransferObjects\UpdateOrderData;
use Domain\Order\Enums\OrderResult;
use Domain\Order\Models\Order;
use Exception;

class UpdateOrderAction
{
    public function execute(Order $order, UpdateOrderData $updateOrderData): OrderResult|Exception
    {
        try {
            if ($updateOrderData->status == "For Cancellation") {
                $order->update([
                    'status' => $updateOrderData->status,
                    'cancelled_reason' => $updateOrderData->notes,
                ]);
            } else {
                $order->update([
                    'status' => $updateOrderData->status,
                    'cancelled_reason' => null,
                ]);
            }

            return OrderResult::SUCCESS;
        } catch (\Exception $e) {
            return $e;
        }
    }
}
