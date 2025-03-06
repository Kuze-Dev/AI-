<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Listeners;

use Domain\Payments\Events\PaymentProcessEvent;
use Domain\Payments\Exceptions\PaymentException;
use Domain\Payments\Models\Payment;
use Domain\ServiceOrder\Actions\ServiceOrderPaymentUpdatedPipelineAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderPaymentUpdatedPipelineData;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
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
        private readonly ServiceOrderPaymentUpdatedPipelineAction $serviceOrderPaymentUpdatedPipelineAction,
    ) {}

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

    private function handleServiceBillStatusUpdate(): void
    {

        $serviceTransactionStatus = match ($this->payment->status) {
            'paid' => ServiceTransactionStatus::PAID,
            'refunded', => ServiceTransactionStatus::REFUNDED,
            'cancelled', => ServiceTransactionStatus::CANCELLED,
            default => throw new PaymentException(),
        };

        $this->serviceTransaction->update(['status' => $serviceTransactionStatus]);

        if ($this->serviceTransaction->is_paid) {
            $this->serviceBill->update(['status' => ServiceBillStatus::PAID]);
        }

        $this->serviceOrderPaymentUpdatedPipelineAction
            ->execute(
                new ServiceOrderPaymentUpdatedPipelineData(
                    service_order: $this->serviceOrder,
                    service_bill: $this->serviceBill,
                    service_transaction: $this->serviceTransaction,
                    is_payment_paid: $this->serviceBill->is_paid && $this->serviceTransaction->is_paid,
                    is_service_order_status_closed: $this->serviceOrder->status == ServiceOrderStatus::CLOSED
                )
            );
    }
}
