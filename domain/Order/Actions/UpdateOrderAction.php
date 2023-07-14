<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use Domain\Order\DataTransferObjects\UpdateOrderData;
use Domain\Order\Enums\OrderResult;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Models\Order;
use Exception;
use Log;

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

            $order->clearMediaCollection('bank_proof_images');
            if ($updateOrderData->bank_proof_medias !== null) {
                foreach ($updateOrderData->bank_proof_medias as $imageUrl) {
                    try {
                        $order->addMediaFromUrl($imageUrl)
                            ->toMediaCollection('bank_proof_images');
                    } catch (Exception $e) {
                        Log::info($e);
                    }
                }
            }

            return OrderResult::SUCCESS;
        } catch (Exception $e) {
            return $e;
        }
    }
}
