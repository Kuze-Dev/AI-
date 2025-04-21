<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use App\Notifications\Order\OrderPlacedNotification;
use App\Settings\OrderSettings;
use Domain\Discount\Actions\CreateDiscountLimitAction;
use Domain\Order\Events\OrderPlacedEvent;
use Domain\Order\Notifications\AdminOrderPlacedMail;
use Domain\Order\Notifications\OrderPlacedMail;
use Domain\Product\Actions\UpdateProductStockAction;
use Illuminate\Support\Facades\Notification;

readonly class OrderPlacedListener
{
    public function __construct(
        private OrderSettings $orderSettings
    ) {}

    /**
     * Handle the event.
     */
    public function handle(OrderPlacedEvent $event): void
    {
        $customer = $event->preparedOrderData->customer;
        $order = $event->order;

        $discount = $event->preparedOrderData->discount;

        // minus the discount
        if (! is_null($discount)) {
            app(CreateDiscountLimitAction::class)->execute($discount, $order, $customer);
        }

        foreach ($order->orderLines as $orderLine) {
            app(UpdateProductStockAction::class)->execute($orderLine->purchasable_type, $orderLine->purchasable_id, $orderLine->quantity, false);
        }

        Notification::send($customer, new OrderPlacedNotification($order));

        $customer->notify(new OrderPlacedMail($order, $event->preparedOrderData));

        $sendEmailToAdmins = $this->orderSettings->admin_should_receive;

        if ($sendEmailToAdmins) {
            $mainReceiver = $this->orderSettings->admin_main_receiver;
            $cc = $this->orderSettings->admin_cc;
            $bcc = $this->orderSettings->admin_bcc;

            Notification::route('mail', $mainReceiver)
                ->notify(new AdminOrderPlacedMail($order, $cc, $bcc));
        }
    }
}
