<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Listeners;

use Domain\ServiceOrder\Actions\ServiceOrderPaymentUpdatedPipelineAction;
use Domain\ServiceOrder\Actions\UpdateServiceBillBalancePartialPaymentAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderPaymentUpdatedPipelineData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Events\AdminServiceOrderBankPaymentEvent;
use Domain\ServiceOrder\Notifications\ServiceOrderBankPaymentNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;

class AdminServiceOrderBankPaymentListener
{
    /**
     * Handle the event.
     */
    public function handle(AdminServiceOrderBankPaymentEvent $event): void
    {
        $serviceOrder = $event->serviceOrder;
        $paymentRemarks = $event->paymentRemarks;

        $payment = $event->payment;

        $serviceTransaction = $serviceOrder->latestTransaction();
        $customer = $serviceOrder->customer;

        if (! $serviceTransaction) {
            throw new ModelNotFoundException();
        }

        $serviceTransaction->update(['status' => ServiceTransactionStatus::PAID]);

        app(UpdateServiceBillBalancePartialPaymentAction::class)->execute($payment, $serviceOrder);

        $serviceBill = $serviceOrder->serviceBills()->first();

        if (is_null($serviceBill)) {
            throw new ModelNotFoundException('no initial service bill found');
        }

        app(ServiceOrderPaymentUpdatedPipelineAction::class)
            ->execute(
                new ServiceOrderPaymentUpdatedPipelineData(
                    service_order: $serviceOrder,
                    service_bill: $serviceBill,
                    service_transaction: $serviceTransaction,
                    is_payment_paid: $serviceBill->is_paid && $serviceTransaction->is_paid,
                    is_service_order_status_closed: $serviceOrder->status == ServiceOrderStatus::CLOSED
                )
            );

        Notification::send($customer, new ServiceOrderBankPaymentNotification($serviceOrder, $paymentRemarks));

    }
}
