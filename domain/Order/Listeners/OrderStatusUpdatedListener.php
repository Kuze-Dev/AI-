<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use App\Notifications\Order\OrderCancelledNotification;
use App\Notifications\Order\OrderFulfilledNotification;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Events\OrderStatusUpdatedEvent;
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
                Notification::send($customer, new OrderCancelledNotification($order));

                break;
            case OrderStatuses::FULFILLED->value:
                Notification::send($customer, new OrderFulfilledNotification($order));

                break;
        }
    }
}
