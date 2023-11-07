<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Listeners;

use Domain\Payments\Events\PaymentProcessEvent;
use Domain\Payments\Models\Payment;
use Domain\ServiceOrder\Actions\CreateServiceTransactionAction;
use Domain\ServiceOrder\Actions\SendToCustomerServiceOrderStatusEmailAction;
use Domain\ServiceOrder\DataTransferObjects\CreateServiceTransactionData;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Jobs\CreateServiceBillJob;
use Domain\ServiceOrder\Jobs\NotifyCustomerLatestServiceBillJob;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;

class ServiceOrderPaymentUpdatedListener
{
    public function __construct(
        private Payment $payment,
        private ServiceBill $serviceBill,
        private CreateServiceTransactionAction $createServiceTransactionAction
    ) {
    }

    public function handle(PaymentProcessEvent $event): void
    {
        $this->payment = $event->payment;

        $payable = $event->payment->payable;

        if ($payable instanceof ServiceBill) {
            $this->serviceBill = $payable;

            match ($event->payment->status) {
                'paid' => $this->onServiceBillPaid(),
                'refunded', => $this->onServiceBillRefunded(),
                'cancelled', => $this->onServiceBillCancelled(),
                default => null
            };
        }
    }

    private function onServiceBillPaid(): void
    {
        $this->createServiceTransactionAction
            ->execute(
                new CreateServiceTransactionData(
                    service_bill: $this->serviceBill,
                    service_transaction_status: ServiceTransactionStatus::PAID,
                    payment: $this->payment
                )
            );

        $this->serviceBill->update(['status' => ServiceBillStatus::PAID]);

        /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
        $serviceOrder = $this->serviceBill->serviceOrder;

        $this->createServiceBill($serviceOrder);

        $this->updateServiceOrderStatus($serviceOrder);
    }

    private function onServiceBillRefunded(): void
    {
        $this->createServiceTransactionAction
            ->execute(
                new CreateServiceTransactionData(
                    service_bill: $this->serviceBill,
                    service_transaction_status: ServiceTransactionStatus::REFUNDED,
                    payment: $this->payment
                )
            );

        $this->serviceBill->update(['status' => ServiceBillStatus::PENDING]);
    }

    private function onServiceBillCancelled(): void
    {
        $this->createServiceTransactionAction
            ->execute(
                new CreateServiceTransactionData(
                    service_bill: $this->serviceBill,
                    service_transaction_status: ServiceTransactionStatus::CANCELLED,
                    payment: $this->payment
                )
            );

        $this->serviceBill->update(['status' => ServiceBillStatus::PENDING]);
    }

    private function createServiceBill(ServiceOrder $serviceOrder): void
    {
        if (
            $serviceOrder->is_subscription &&
            ! $serviceOrder->is_auto_generated_bill
        ) {
            /** @var \Domain\Customer\Models\Customer $customer */
            $customer = $serviceOrder->customer;

            CreateServiceBillJob::dispatch(
                $serviceOrder,
                $this->serviceBill
            )->chain([
                new NotifyCustomerLatestServiceBillJob(
                    $customer,
                    $serviceOrder
                ),
            ]);
        }
    }

    private function updateServiceOrderStatus(ServiceOrder $serviceOrder): void
    {
        if ($serviceOrder->status != ServiceOrderStatus::CLOSED) {
            $serviceOrder->update([
                'status' => $serviceOrder->is_subscription
                    ? ServiceOrderStatus::ACTIVE
                    : ServiceOrderStatus::PENDING,
            ]);

            app(SendToCustomerServiceOrderStatusEmailAction::class)
                ->onQueue()
                ->execute($serviceOrder);
        }
    }
}
