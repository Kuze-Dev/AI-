<?php

declare(strict_types=1);

namespace Domain\Order\Actions;

use App\Notifications\Order\OrderPlacedNotification;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Exception;
use Notification;

class PlaceOrderAction
{
    public function execute(PlaceOrderData $placeOrderData): array|Exception
    {
        $payload = app(PrepareOrderAction::class)
            ->execute($placeOrderData);

        if ($payload instanceof PreparedOrderData) {
            $result = app(SplitOrderAction::class)->execute($payload, $placeOrderData);


            $customer = auth()->user();
            // Notification::send($customer, new OrderPlacedNotification($result['order']));

            return $result;
        }
    }
}
