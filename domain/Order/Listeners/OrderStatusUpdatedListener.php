<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use Domain\Order\Events\OrderStatusUpdatedEvent;
use Domain\Order\Notifications\OrderStatusUpdatedMail;

class OrderStatusUpdatedListener
{
    /**
     * Handle the event.
     *
     * @param  \Domain\Order\Events\OrderStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(OrderStatusUpdatedEvent $event)
    {
        $customer = $event->customer;
        $order = $event->order;

        $customer->notify(new OrderStatusUpdatedMail($order, $event->status, $event->emailRemarks));
    }
}
