<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Listeners;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Events\AdminServiceBillBankPaymentEvent;
use Domain\ServiceOrder\Notifications\ServiceBillBankPaymentNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;

class AdminServiceBillBankPaymentListener
{
    /**
     * Handle the event.
     */
    public function handle(AdminServiceBillBankPaymentEvent $event): void
    {
        $serviceBill = $event->serviceBill;
        $paymentRemarks = $event->paymentRemarks;

        $serviceTransaction = $serviceBill->latestTransaction();
        $serviceOrder = $serviceBill->serviceOrder;
        $customer = $serviceOrder?->customer;

        if (! $serviceOrder || ! $serviceTransaction) {
            throw new ModelNotFoundException;
        }

        if ($serviceOrder->is_subscription) {
            $serviceOrder->update([
                'status' => ServiceOrderStatus::ACTIVE,
            ]);
        } else {
            $serviceOrder->update([
                'status' => ServiceOrderStatus::INPROGRESS,
            ]);
        }

        $serviceTransaction->update([
            'status' => ServiceTransactionStatus::PAID,
        ]);

        Notification::send($customer, new ServiceBillBankPaymentNotification($serviceBill, $paymentRemarks));

    }
}
