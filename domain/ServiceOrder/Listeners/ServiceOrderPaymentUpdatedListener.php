<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Listeners;

use Domain\Payments\Events\PaymentProcessEvent;
use Domain\Payments\Exceptions\PaymentException;
use Domain\Payments\Models\Payment;
use Domain\ServiceOrder\Actions\GetServiceBillingAndDueDateAction;
use Domain\ServiceOrder\DataTransferObjects\GetServiceBillingAndDueData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderPaymentData;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Jobs\CreateServiceBillJob;
use Domain\ServiceOrder\Jobs\NotifyCustomerLatestServiceBillJob;
use Domain\ServiceOrder\Jobs\NotifyCustomerServiceOrderStatusJob;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class ServiceOrderPaymentUpdatedListener
{
    public function __construct(
        private Payment $payment,
        private ServiceBill $serviceBill,
        private ServiceOrder $serviceOrder,
        private ServiceTransaction $serviceTransaction,
        private GetServiceBillingAndDueDateAction $getServiceBillingAndDueDateAction
    ) {
    }

    /** @throws Throwable */
    public function handle(PaymentProcessEvent $event): void
    {
        $this->payment = $event->payment;

        $payable = $event->payment->payable;

        if (! ($payable instanceof ServiceBill)) {
            return;
        }

        $this->serviceBill = $payable;

        $this->serviceOrder = $this->prepareServiceOrder();

        $this->serviceTransaction = $this->prepareServiceTransaction();

        $this->handleServiceBillStatusUpdate();
    }

    private function prepareServiceOrder(): ServiceOrder
    {
        $serviceOrder = $this->serviceBill->serviceOrder;

        if (is_null($serviceOrder)) {
            throw new ModelNotFoundException(trans('No service order found'));
        }

        return $serviceOrder;
    }

    private function prepareServiceTransaction(): ServiceTransaction
    {
        $serviceTransaction = $this->serviceBill
            ->serviceTransactions()
            ->wherePaymentId($this->payment->id)
            ->first();

        if (is_null($serviceTransaction)) {
            throw new ModelNotFoundException(trans('No service transaction found'));
        }

        return $serviceTransaction;
    }

    private function onServiceBillPaid(): ServiceOrderPaymentData
    {
        return new ServiceOrderPaymentData(
            service_transaction_status: ServiceTransactionStatus::PAID,
            service_bill_status: ServiceBillStatus::PAID
        );
    }

    private function onServiceBillRefunded(): ServiceOrderPaymentData
    {
        return new ServiceOrderPaymentData(
            service_transaction_status: ServiceTransactionStatus::REFUNDED,
            service_bill_status: ServiceBillStatus::PENDING
        );
    }

    private function onServiceBillCancelled(): ServiceOrderPaymentData
    {
        return new ServiceOrderPaymentData(
            service_transaction_status: ServiceTransactionStatus::CANCELLED,
            service_bill_status: ServiceBillStatus::PENDING
        );
    }

    private function createServiceBill(): void
    {
        $shouldCreateNewServiceBill = $this->serviceOrder->is_subscription &&
        ! $this->serviceOrder->is_auto_generated_bill;

        if (! $shouldCreateNewServiceBill) {
            return;
        }

        /** @var \Illuminate\Foundation\Bus\PendingDispatch $createServiceBillJob */
        $createServiceBillJob = CreateServiceBillJob::dispatchIf(
            $shouldCreateNewServiceBill,
            $this->serviceOrder,
            $this->getServiceBillingAndDueDateAction
                ->execute(
                    new GetServiceBillingAndDueData(
                        service_order: $this->serviceOrder,
                        service_bill: $this->serviceBill,
                        service_transaction: $this->serviceTransaction
                    )
                )
        );

        $createServiceBillJob->chain([
            new NotifyCustomerLatestServiceBillJob($this->serviceOrder),
        ]);
    }

    private function updateServiceOrderStatus(): void
    {
        $this->serviceOrder->update([
            'status' => $this->serviceOrder->is_subscription
                ? ServiceOrderStatus::ACTIVE
                : ServiceOrderStatus::PENDING,
        ]);

        NotifyCustomerServiceOrderStatusJob::dispatch($this->serviceOrder);
    }

    private function handleServiceBillStatusUpdate(): void
    {
        /** @var \Domain\ServiceOrder\DataTransferObjects\ServiceOrderPaymentData $serviceOrderPaymentData */
        $serviceOrderPaymentData = match ($this->payment->status) {
            'paid' => $this->onServiceBillPaid(),
            'refunded', => $this->onServiceBillRefunded(),
            'cancelled', => $this->onServiceBillCancelled(),
            default => throw new PaymentException()
        };

        $this->serviceTransaction->update([
            'status' => $serviceOrderPaymentData->service_transaction_status,
        ]);

        $this->serviceBill->update([
            'status' => $serviceOrderPaymentData->service_bill_status,
        ]);

        if (! $this->serviceBill->is_paid || ! $this->serviceTransaction->is_paid) {
            return;
        }

        if ($this->serviceOrder->status == ServiceOrderStatus::CLOSED) {
            return;
        }

        $this->createServiceBill();

        $this->updateServiceOrderStatus();
    }
}
