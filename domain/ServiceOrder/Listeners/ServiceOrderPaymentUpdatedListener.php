<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Listeners;

use Domain\Payments\Events\PaymentProcessEvent;
use Domain\ServiceOrder\Actions\ChangeServiceOrderStatusAction;
use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\Actions\GetServiceBillingAndDueDateAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceTransaction;

class ServiceOrderPaymentUpdatedListener
{
    /**
     * Handle the event.
     *
     * @param  \Domain\Payments\Events\PaymentProcessEvent  $event
     * @return void
     */
    public function handle(PaymentProcessEvent $event): void
    {
        if ($event->payment->payable instanceof ServiceBill) {
            $status = $event->payment->status;
            $order = $event->payment->payable;

            match ($status) {
                'paid' => $this->onServiceBillPaid($order),
                default => null
            };
        }
    }

    private function onServiceBillPaid(ServiceBill $serviceBill): void
    {
        $serviceTransaction = ServiceTransaction::whereServiceBillId($serviceBill->id)->firstOrFail();

        $serviceTransaction->update([
            'status' => ServiceTransactionStatus::PAID,
        ]);

        $serviceBill->update([
            'status' => ServiceBillStatus::PAID,
        ]);

        if($serviceBill->serviceOrder->service->is_subscription) {
            $serviceBillingDate = app(GetServiceBillingAndDueDateAction::class)->execute($serviceBill);
            app(CreateServiceBillAction::class)->execute(ServiceBillData::fromCreatedServiceOrder($serviceBill->serviceOrder->toArray()), $serviceBillingDate);
        }

        if ($serviceBill->serviceOrder->service->is_subscription) {
            $serviceBill->serviceOrder->update([
                'status' => ServiceOrderStatus::ACTIVE,
            ]);
        } else {
            $serviceBill->serviceOrder->update([
                'status' => ServiceOrderStatus::PENDING,
            ]);
        }

        app(ChangeServiceOrderStatusAction::class)->execute($serviceBill->serviceOrder);
    }
}
