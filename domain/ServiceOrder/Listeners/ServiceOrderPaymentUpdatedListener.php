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

            $serviceBill = $event->payment->payable;

            match ($status) {
                'paid' => $this->onServiceBillPaid($serviceBill),
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

        /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
        $serviceOrder = $serviceBill->serviceOrder;

        /** @var \Domain\Service\Models\Service $service */
        $service = $serviceOrder->service;

        if (
            $service->is_subscription &&
            ! $service->is_auto_generated_bill
        ) {
            $serviceBillingDate = app(GetServiceBillingAndDueDateAction::class)
                ->execute($serviceBill);

            app(CreateServiceBillAction::class)
                ->execute(
                    ServiceBillData::fromCreatedServiceOrder(
                        $serviceOrder->toArray()
                    ),
                    $serviceBillingDate
                );
        }

        $serviceOrder->update([
            'status' => $service->is_subscription
                ? ServiceOrderStatus::ACTIVE
                : ServiceOrderStatus::PENDING,
        ]);

        app(ChangeServiceOrderStatusAction::class)->execute($serviceOrder);
    }
}
