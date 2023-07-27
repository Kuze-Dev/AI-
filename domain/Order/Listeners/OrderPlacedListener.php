<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use Domain\Order\Events\OrderPlacedEvent;
use Domain\Order\Notifications\OrderPlacedMail;

class OrderPlacedListener
{
    /**
     * Handle the event.
     *
     * @param  \Domain\Order\Events\OrderPlacedEvent  $event
     * @return void
     */
    public function handle(OrderPlacedEvent $event)
    {
        $customer = $event->customer;
        $order = $event->order;
        $shippingAddress = $event->shippingAddress;
        $shippingMethod = $event->shippingMethod;

        $customer->notify(new OrderPlacedMail($order, $shippingAddress, $shippingMethod));
    }
}
