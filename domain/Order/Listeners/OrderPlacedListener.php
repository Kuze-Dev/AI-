<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use App\Notifications\Order\OrderPlacedNotification;
use Domain\Order\Events\OrderPlacedEvent;
use Domain\Order\Notifications\OrderPlacedMail;
use Illuminate\Support\Facades\Notification;

class OrderPlacedListener
{
    /**
     * Handle the event.
     *
     * @param  \Domain\Order\Events\OrderPlacedEvent  $event
     * @return void
     */
    public function handle(OrderPlacedEvent $event): void
    {
        $customer = $event->customer;
        $order = $event->order;
        $shippingAddress = $event->shippingAddress;
        $shippingMethod = $event->shippingMethod;

        Notification::send($customer, new OrderPlacedNotification($order));

        //comment when the env and mail is not set
        // $customer->notify(new OrderPlacedMail($order, $shippingAddress, $shippingMethod));
    }
}
