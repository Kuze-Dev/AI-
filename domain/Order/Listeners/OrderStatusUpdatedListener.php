<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use Domain\Order\Enums\OrderNotifications;
use Domain\Order\Events\OrderPlacedEvent;
use Domain\Order\Notifications\OrderUpdatedInvoice;

class OrderStatusUpdatedListener
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

        $customer->notify(new OrderUpdatedInvoice($order, OrderNotifications::UPDATED));
    }
}
