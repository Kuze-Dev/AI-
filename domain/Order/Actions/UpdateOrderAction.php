<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Media\Actions\CreateMediaAction;
use Domain\Order\DataTransferObjects\UpdateOrderData;
use Domain\Order\Enums\OrderResult;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Models\Order;
use Exception;

class UpdateOrderAction
{
    public function execute(Order $order, UpdateOrderData $updateOrderData): OrderResult|Exception
    {
        try {
            if ($updateOrderData->status) {
                if ($updateOrderData->status == 'For Cancellation') {
                    //cant cancel if order is 
                    if ($order->status != OrderStatuses::PENDING) {
                        return OrderResult::FAILED;
                    }

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
            }

            if ($updateOrderData->bank_proof_medias !== null) {
                app(CreateMediaAction::class)
                    ->execute($order, $updateOrderData->bank_proof_medias, 'bank_proof_images');
            }

            return OrderResult::SUCCESS;
        } catch (Exception $e) {
            return $e;
        }
    }
}
