<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Listeners;

use Domain\Payments\Events\PaymentProcessEvent;
use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\Actions\GetServiceBillingAndDueDateAction;
use Domain\ServiceOrder\Actions\SendToCustomerServiceOrderStatusEmailAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceTransaction;

class ServiceOrderPaymentUpdatedListener
{
    public function __construct(
        private CreateServiceBillAction $createServiceBillAction,
        private GetServiceBillingAndDueDateAction $getServiceBillingAndDueDateAction,
        private SendToCustomerServiceOrderStatusEmailAction $sendToCustomerServiceOrderStatusEmailAction
    ) {
    }

    public function handle(PaymentProcessEvent $event): void
    {
        if ($event->payment->payable instanceof ServiceBill) {
            $status = $event->payment->status;

            $serviceBill = $event->payment->payable;

            match ($status) {
                'paid' => $this->onServiceBillPaid($serviceBill),
                'refunded', => $this->onServiceBillRefunded($serviceBill),
                'cancelled', => $this->onServiceBillCancelled($serviceBill),
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

        if (
            $serviceOrder->is_subscription &&
            ! $serviceOrder->is_auto_generated_bill
        ) {
            $this->createServiceBillAction
                ->execute(
                    ServiceBillData::subsequentFromServiceOrderWithAssignedDates(
                        serviceOrder: $serviceOrder,
                        serviceOrderBillingAndDueDateData: $this->getServiceBillingAndDueDateAction
                            ->execute($serviceBill)
                    )
                );
        }

        $serviceOrder->update([
            'status' => $serviceOrder->is_subscription
                ? ServiceOrderStatus::ACTIVE
                : ServiceOrderStatus::PENDING,
        ]);

        $this->sendToCustomerServiceOrderStatusEmailAction
            ->execute($serviceOrder);
    }

    private function onServiceBillRefunded(ServiceBill $serviceBill): void
    {
        $serviceTransaction = ServiceTransaction::whereServiceBillId($serviceBill->id)->firstOrFail();

        $serviceTransaction->update([
            'status' => ServiceTransactionStatus::REFUNDED,
        ]);

        $serviceBill->update([
            'status' => ServiceBillStatus::PENDING,
        ]);

    }

    private function onServiceBillCancelled(ServiceBill $serviceBill): void
    {
        $serviceTransaction = ServiceTransaction::whereServiceBillId($serviceBill->id)->firstOrFail();

        $serviceTransaction->update([
            'status' => ServiceTransactionStatus::CANCELLED,
        ]);

        $serviceBill->update([
            'status' => ServiceBillStatus::PENDING,
        ]);

    }
}
