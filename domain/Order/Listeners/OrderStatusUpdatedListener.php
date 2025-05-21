<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use App\Notifications\Order\OrderCancelledNotification;
use App\Notifications\Order\OrderFulfilledNotification;
use Domain\Discount\Actions\DiscountHelperFunctions;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Events\OrderStatusUpdatedEvent;
use Domain\Product\Actions\UpdateProductStockAction;
use Domain\RewardPoint\Actions\EarnPointAction;
use Illuminate\Support\Facades\Notification;

class OrderStatusUpdatedListener
{
    /**
     * Handle the event.
     */
    public function handle(OrderStatusUpdatedEvent $event): void
    {
        $customer = $event->customer;
        $order = $event->order;
        $status = $event->status;

        switch ($status) {
            case OrderStatuses::CANCELLED->value:

                app(DiscountHelperFunctions::class)->resetDiscountUsage($order);

                foreach ($order->orderLines as $orderLine) {
                    app(UpdateProductStockAction::class)->execute($orderLine->purchasable_type, $orderLine->purchasable_id, $orderLine->quantity, true);
                }

                if ($customer) {
                    Notification::send($customer, new OrderCancelledNotification($order));
                }

                break;
            case OrderStatuses::FULFILLED->value:
                if ($customer) {
                    Notification::send($customer, new OrderFulfilledNotification($order));

                    // if ( TenantFeatureSupport::active(RewardPoints::class)) {
                    app(EarnPointAction::class)->execute($customer, $order);

                    // }
                }

                break;
        }
    }
}
