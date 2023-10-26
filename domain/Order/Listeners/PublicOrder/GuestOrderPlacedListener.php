<?php

declare(strict_types=1);

namespace Domain\Order\Listeners\PublicOrder;

use Domain\Discount\Actions\CreateDiscountLimitAction;
use Domain\Order\Events\PublicOrder\GuestOrderPlacedEvent;
use Domain\Order\Notifications\OrderPlacedMail;
use Domain\Product\Actions\UpdateProductStockAction;
use Illuminate\Support\Facades\Notification;

class GuestOrderPlacedListener
{
    /**
     * Handle the event.
     */
    public function handle(GuestOrderPlacedEvent $event): void
    {
        $email = $event->guestPreparedOrderData->customer->email;
        $order = $event->order;

        $discount = $event->guestPreparedOrderData->discount;

        // minus the discount
        if (! is_null($discount)) {
            app(CreateDiscountLimitAction::class)->execute($discount, $order, null);
        }

        foreach ($order->orderLines as $orderLine) {
            app(UpdateProductStockAction::class)->execute(
                $orderLine->purchasable_type,
                $orderLine->purchasable_id,
                $orderLine->quantity,
                false
            );
        }

        Notification::route('mail', $email)
            ->notify(new OrderPlacedMail($order, $event->guestPreparedOrderData));
    }
}
