<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use App\Notifications\Order\OrderPlacedNotification;
use Domain\Discount\Actions\CreateDiscountLimitAction;
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
        $customer = $event->preparedOrderData->customer;
        $order = $event->order;
        $shippingAddress = $event->preparedOrderData->shippingAddress;
        $shippingMethod = $event->preparedOrderData->shippingMethod;

        $discount = $event->preparedOrderData->discount;
        Notification::send($customer, new OrderPlacedNotification($order));

        //comment when the env and mail is not set
        $customer->notify(new OrderPlacedMail($order, $shippingAddress, $shippingMethod));

        // minus the discount
        if ( ! is_null($discount)) {
            app(CreateDiscountLimitAction::class)->execute($discount, $order, $customer);
        }

        // minus the product stocks
    }
}
