<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use App\Notifications\Order\OrderCancelledNotification;
use Domain\Customer\Models\Customer;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Models\Order;
use Domain\Order\Notifications\AdminOrderStatusUpdatedMail;
use Domain\Payments\Events\PaymentProcessEvent;
use Illuminate\Support\Facades\Notification;

class OrderPaymentUpdatedListener
{
    /**
     * Handle the event.
     *
     * @param  \Domain\Payments\Events\PaymentProcessEvent  $event
     * @return void
     */
    public function handle(PaymentProcessEvent $event): void
    {
        if ($event->payment->payable instanceof Order) {
            $status = $event->payment->status;
            $order = $event->payment->payable;

            match ($status) {
                'paid' => $this->onOrderPaid($order),
                'cancelled' => $this->onOrderCancelled($order),
                default => null
            };
        }
    }

    private function onOrderPaid(Order $order): void
    {
        $order->update([
            'is_paid' => true,
            'status' => OrderStatuses::PROCESSING,
        ]);
    }

    private function onOrderCancelled(Order $order): void
    {
        $order->update([
            'status' => OrderStatuses::CANCELLED,
        ]);

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Customer::find($order->customer_id);

        Notification::send($customer, new OrderCancelledNotification($order));

        // off muna for now
        // $customer->notify(new AdminOrderStatusUpdatedMail(
        //     $order,
        //     'cancelled',
        //     ''
        // ));

        // back the discount

        // back the product stock
    }
}
