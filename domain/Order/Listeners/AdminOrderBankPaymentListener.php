<?php

declare(strict_types=1);

namespace Domain\Order\Listeners;

use App\Notifications\Order\OrderBankPaymentNotification;
use Domain\Order\Events\AdminOrderBankPaymentEvent;
use Illuminate\Support\Facades\Notification;

class AdminOrderBankPaymentListener
{
    /**
     * Handle the event.
     *
     * @param  \Domain\Order\Events\AdminOrderBankPaymentEvent  $event
     * @return void
     */
    public function handle(AdminOrderBankPaymentEvent $event): void
    {
        $customer = $event->customer;
        $order = $event->order;
        $paymentRemarks = $event->paymentRemarks;

        Notification::send($customer, new OrderBankPaymentNotification($order, $paymentRemarks));
    }
}
