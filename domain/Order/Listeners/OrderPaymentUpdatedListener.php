<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use Domain\Customer\Models\Customer;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Events\OrderStatusUpdatedEvent;
use Domain\Order\Models\Order;
use Domain\Payments\Events\PaymentProcessEvent;

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
                "cancel" => $this->onOrderCancelled($order),
                default => null
            };
        }
    }

    private function onOrderPaid(Order $order): void
    {
        $order->update([
            'is_paid' => true,
        ]);
    }

    private function onOrderCancelled(Order $order)
    {
        $order->update([
            'status' => OrderStatuses::CANCELLED,
        ]);

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Customer::find($order->customer_id);

        event(new OrderStatusUpdatedEvent(
            $customer,
            $order,
            'cancelled'
        ));
    }
}
