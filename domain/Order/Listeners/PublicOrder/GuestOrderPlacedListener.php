<?php

declare(strict_types=1);

namespace Domain\Order\Listeners\PublicOrder;

use App\Notifications\Order\OrderPlacedNotification;
use Domain\Discount\Actions\CreateDiscountLimitAction;
use Domain\Order\Events\PublicOrder\GuestOrderPlacedEvent;
use Domain\Order\Notifications\OrderPlacedMail;
use Domain\Product\Actions\UpdateProductStockAction;
use Illuminate\Support\Facades\Notification;

class GuestOrderPlacedListener
{
    /**
     * Handle the event.
     *
     * @param  \Domain\Order\Events\PublicOrder\GuestOrderPlacedEvent $event
     * @return void
     */
    public function handle(GuestOrderPlacedEvent $event): void
    {
        $order = $event->order;
        $shippingAddress = $event->guestPreparedOrderData->shippingAddress;
        $shippingMethod = $event->guestPreparedOrderData->shippingMethod;

        // $discount = $event->guestPreparedOrderData->discount;

        Notification::route('mail', 'john@doe.com')
            ->notify(new OrderPlacedMail($order, $shippingAddress, $shippingMethod));

        // minus the discount
        // TODO: make this helper accepts null customer
        // if (!is_null($discount)) {
        //     app(CreateDiscountLimitAction::class)->execute($discount, $order, $customer);
        // }

        foreach ($order->orderLines as $orderLine) {
            app(UpdateProductStockAction::class)->execute($orderLine->purchasable_type, $orderLine->purchasable_id, $orderLine->quantity, false);
        }
    }
}
