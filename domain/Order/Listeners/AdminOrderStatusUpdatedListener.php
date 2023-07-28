<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use App\Notifications\Order\OrderCancelledNotification;
use App\Notifications\Order\OrderDeliveredNotification;
use App\Notifications\Order\OrderFulfilledNotification;
use App\Notifications\Order\OrderPackedNotification;
use App\Notifications\Order\OrderRefundedNotification;
use App\Notifications\Order\OrderShippedNotification;
use Domain\Order\Events\AdminOrderStatusUpdatedEvent;
use Domain\Order\Notifications\AdminOrderStatusUpdatedMail;
use Illuminate\Support\Facades\Notification;

class AdminOrderStatusUpdatedListener
{
    /**
     * Handle the event.
     *
     * @param  \Domain\Order\Events\AdminOrderStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(AdminOrderStatusUpdatedEvent $event): void
    {
        $customer = $event->customer;
        $order = $event->order;

        switch ($event->status) {
            case 'Cancelled':
                Notification::send($customer, new OrderCancelledNotification($order));

                break;
            case 'Refunded':
                Notification::send($customer, new OrderRefundedNotification($order));

                break;
            case 'Packed':
                Notification::send($customer, new OrderPackedNotification($order));

                break;
            case 'Shipped':
                Notification::send($customer, new OrderShippedNotification($order));

                break;
            case 'Delivered':
                Notification::send($customer, new OrderDeliveredNotification($order));

                break;
            case 'Fulfilled':
                Notification::send($customer, new OrderFulfilledNotification($order));

                break;
        }

        if ($event->shouldSendEmail) {
            $customer->notify(new AdminOrderStatusUpdatedMail($order, $event->status, $event->emailRemarks));
        }
    }
}
