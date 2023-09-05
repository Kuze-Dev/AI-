<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use App\Notifications\Order\OrderCancelledNotification;
use App\Notifications\Order\OrderFulfilledNotification;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Events\OrderStatusUpdatedEvent;
use Domain\Product\Actions\UpdateProductStockAction;
use Illuminate\Support\Facades\Notification;

class OrderStatusUpdatedListener
{
    /**
     * Handle the event.
     *
     * @param  \Domain\Order\Events\OrderStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(OrderStatusUpdatedEvent $event): void
    {
        $customer = $event->customer;
        $order = $event->order;
        $status = $event->status;

        switch ($status) {
            case OrderStatuses::CANCELLED->value:
                foreach ($order->orderLines as $orderLine) {
                    app(UpdateProductStockAction::class)->execute($orderLine->purchasable_type, $orderLine->purchasable_id, $orderLine->quantity, true);
                }

                Notification::send($customer, new OrderCancelledNotification($order));

                break;
            case OrderStatuses::FULFILLED->value:
                Notification::send($customer, new OrderFulfilledNotification($order));
                // if ( tenancy()->tenant?->features()->active(RewardPoints::class)) {
                app(EarnPointAction::class)->execute($customer, $order);

                // }

                break;
        }
    }
}
