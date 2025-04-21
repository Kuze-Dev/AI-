<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use App\Notifications\Order\OrderCancelledNotification;
use Domain\Customer\Models\Customer;
use Domain\Discount\Actions\DiscountHelperFunctions;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Models\Order;
use Domain\Order\Notifications\AdminOrderStatusUpdatedMail;
use Domain\Payments\Events\PaymentProcessEvent;
use Domain\Product\Actions\UpdateProductStockAction;
use Illuminate\Support\Facades\Notification;

class OrderPaymentUpdatedListener
{
    /**
     * Handle the event.
     */
    public function handle(PaymentProcessEvent $event): void
    {
        if ($event->payment->payable instanceof Order) {
            $status = $event->payment->status;
            $order = $event->payment->payable;

            match ($status) {
                'paid' => $this->onOrderPaid($order),
                'cancelled' => $this->onOrderCancelled($order),
                default => null
            };
        }
    }

    private function onOrderPaid(Order $order): void
    {
        $order->update([
            'is_paid' => true,
            'status' => OrderStatuses::PROCESSING,
        ]);
    }

    private function onOrderCancelled(Order $order): void
    {
        $order->update([
            'status' => OrderStatuses::CANCELLED,
        ]);

        /** @var \Domain\Customer\Models\Customer|null $customer */
        $customer = Customer::find($order->customer_id);

        if ($customer) {

            Notification::send($customer, new OrderCancelledNotification($order));

            // comment when the env and mail is not set
            $customer->notify(new AdminOrderStatusUpdatedMail(
                $order,
                'cancelled',
                ''
            ));
        }

        app(DiscountHelperFunctions::class)->resetDiscountUsage($order);

        // back the product stock
        foreach ($order->orderLines as $orderLine) {
            app(UpdateProductStockAction::class)->execute($orderLine->purchasable_type, $orderLine->purchasable_id, $orderLine->quantity, true);
        }
    }
}
