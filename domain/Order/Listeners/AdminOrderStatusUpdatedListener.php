<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use App\Notifications\Order\OrderCancelledNotification;
use App\Notifications\Order\OrderDeliveredNotification;
use App\Notifications\Order\OrderFulfilledNotification;
use App\Notifications\Order\OrderPackedNotification;
use App\Notifications\Order\OrderRefundedNotification;
use App\Notifications\Order\OrderShippedNotification;
use Domain\Discount\Actions\DiscountHelperFunctions;
use Domain\Order\Enums\OrderStatuses;
use Domain\Order\Events\AdminOrderStatusUpdatedEvent;
use Domain\Order\Notifications\AdminOrderStatusUpdatedMail;
use Domain\Product\Actions\UpdateProductStockAction;
use Domain\RewardPoint\Actions\EarnPointAction;
use Illuminate\Support\Facades\Notification;

class AdminOrderStatusUpdatedListener
{
    /**
     * Handle the event.
     */
    public function handle(AdminOrderStatusUpdatedEvent $event): void
    {
        $customer = $event->customer;
        // $order = $event->order;

        if ($customer) {
            $this->notifyRegisteredCustomer($event);
        } else {
            $this->notifyGuestCustomer($event);
        }
    }

    private function notifyRegisteredCustomer(AdminOrderStatusUpdatedEvent $event): void
    {
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = $event->customer;
        $order = $event->order;

        switch ($event->status) {
            case OrderStatuses::CANCELLED->value:
                // back the discount
                app(DiscountHelperFunctions::class)->resetDiscountUsage($order);

                // back the product stock
                foreach ($order->orderLines as $orderLine) {
                    app(UpdateProductStockAction::class)->execute($orderLine->purchasable_type, $orderLine->purchasable_id, $orderLine->quantity, true);
                }

                Notification::send($customer, new OrderCancelledNotification($order));

                break;
            case OrderStatuses::REFUNDED->value:
                // back the discount
                app(DiscountHelperFunctions::class)->resetDiscountUsage($order);

                // back the product stock
                foreach ($order->orderLines as $orderLine) {
                    app(UpdateProductStockAction::class)->execute($orderLine->purchasable_type, $orderLine->purchasable_id, $orderLine->quantity, true);
                }

                Notification::send($customer, new OrderRefundedNotification($order));

                break;
            case OrderStatuses::PACKED->value:
                Notification::send($customer, new OrderPackedNotification($order));

                break;
            case OrderStatuses::SHIPPED->value:
                Notification::send($customer, new OrderShippedNotification($order));

                break;
            case OrderStatuses::DELIVERED->value:
                Notification::send($customer, new OrderDeliveredNotification($order));

                break;
            case OrderStatuses::FULFILLED->value:
                Notification::send($customer, new OrderFulfilledNotification($order));
                // if ( TenantFeatureSupport::active(RewardPoints::class)) {
                app(EarnPointAction::class)->execute($customer, $order);

                // }

                break;
        }

        if ($event->shouldSendEmail) {
            $customer->notify(new AdminOrderStatusUpdatedMail($order, $event->status, $event->emailRemarks));
        }
    }

    private function notifyGuestCustomer(AdminOrderStatusUpdatedEvent $event): void
    {
        $customerEmail = $event->order->customer_email;
        $order = $event->order;

        switch ($event->status) {
            case OrderStatuses::CANCELLED->value:
                // back the discount
                app(DiscountHelperFunctions::class)->resetDiscountUsage($order);

                // back the product stock
                foreach ($order->orderLines as $orderLine) {
                    app(UpdateProductStockAction::class)->execute($orderLine->purchasable_type, $orderLine->purchasable_id, $orderLine->quantity, true);
                }

                break;
            case OrderStatuses::REFUNDED->value:
                // back the discount
                app(DiscountHelperFunctions::class)->resetDiscountUsage($order);

                // back the product stock
                foreach ($order->orderLines as $orderLine) {
                    app(UpdateProductStockAction::class)->execute($orderLine->purchasable_type, $orderLine->purchasable_id, $orderLine->quantity, true);
                }

                break;
        }

        if ($event->shouldSendEmail) {
            Notification::route('mail', $customerEmail)
                ->notify(new AdminOrderStatusUpdatedMail($order, $event->status, $event->emailRemarks));
        }
    }
}
