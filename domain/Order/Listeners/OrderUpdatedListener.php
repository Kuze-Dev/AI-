<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use Domain\Order\Actions\DestroyOrderAction;
use Domain\Order\Models\Order;
use Domain\Payments\Events\PaymentProcessEvent;

class OrderUpdatedListener
{
    public function handle(PaymentProcessEvent $event)
    {
        if ($event->payment->payable instanceof Order) {
            $status = $event->payment->status;
            $order = $event->payment->payable;

            match ($status) {
                "paid" => $this->onOrderPaid($order),
                    // "cancel" => $this->onOrderCancelled($order),
                default => null
            };
        }
    }

    private function onOrderPaid(Order $order)
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
