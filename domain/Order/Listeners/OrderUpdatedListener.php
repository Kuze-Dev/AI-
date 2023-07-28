<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use Domain\Order\Models\Order;
use Domain\Payments\Events\PaymentProcessEvent;

class OrderUpdatedListener
{
    /**
     * Handle the event.
     *
     * @param  \Domain\Payments\Events\PaymentProcessEvent  $event
     * @return void
     */
    public function handle(PaymentProcessEvent $event)
    {
        if ($event->payment->payable instanceof Order) {
            $status = $event->payment->status;
            $order = $event->payment->payable;

            match ($status) {
                'paid' => $this->onOrderPaid($order),
                // "cancel" => $this->onOrderCancelled($order),
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

    // private function onOrderCancelled(Order $order)
    // {
    //     //add balik ng discount
    //     app(DestroyOrderAction::class)->execute($order);
    // }
}
