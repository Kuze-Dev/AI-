<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Actions\CreatePaymentAction;
use Domain\Payments\DataTransferObjects\AmountData;
use Domain\Payments\DataTransferObjects\CreatepaymentData;
use Domain\Payments\DataTransferObjects\PaymentDetailsData;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\TransactionData;
use Domain\Payments\Exceptions\PaymentException;
use Domain\Payments\Models\Payment;
use Domain\ServiceOrder\DataTransferObjects\CheckoutServiceOrderData;
use Domain\ServiceOrder\DataTransferObjects\CreateServiceTransactionData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Exceptions\ServiceOrderStatusStillPendingException;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class CheckoutServiceOrderAction
{
    public function __construct(
        private CheckoutServiceOrderData $checkoutServiceOrderData,
        private PaymentMethod $paymentMethod,
        private ServiceBill $serviceBill,
        private ServiceOrder $serviceOrder,
        private CreatePaymentAction $createPaymentAction,
        private CreateServiceTransactionAction $createServiceTransactionAction
    ) {
    }

    /** @throws Throwable */
    public function execute(CheckoutServiceOrderData $checkoutServiceOrderData): PaymentAuthorize
    {
        $this->checkoutServiceOrderData = $checkoutServiceOrderData;

        $this->paymentMethod = $this->preparePaymentMethod();

        $this->serviceBill = $this->prepareServiceBill();

        $this->serviceOrder = $this->prepareServiceOrder();

        return $this->proceedPayment();
    }

    /** @throws Throwable */
    private function preparePaymentMethod(): PaymentMethod
    {
        $paymentMethod = PaymentMethod::whereSlug(
            $this->checkoutServiceOrderData->payment_method
        )
            ->first();

        if (is_null($paymentMethod)) {
            throw new ModelNotFoundException(trans('No payment method found'));
        }

        return $paymentMethod;
    }

    /** @throws Throwable */
    private function prepareServiceBill(): ServiceBill
    {
        $serviceBill = ServiceBill::whereReference(
            $this->checkoutServiceOrderData->reference_id
        )
            ->first();

        if (is_null($serviceBill)) {
            throw new ModelNotFoundException(trans('No service bill found'));
        }

        return $serviceBill;
    }

    /** @throws Throwable */
    private function prepareServiceOrder(): ServiceOrder
    {
        $serviceOrder = $this->serviceBill->serviceOrder;

        if (is_null($serviceOrder)) {
            throw new ModelNotFoundException(trans('No service order found'));
        }

        return $serviceOrder;
    }

    /** @throws Throwable */
    private function proceedPayment(): PaymentAuthorize
    {
        if ($this->serviceOrder->status === ServiceOrderStatus::PENDING) {
            throw new ServiceOrderStatusStillPendingException();
        }

        $providerData = new CreatepaymentData(
            transactionData: TransactionData::fromArray(
                [
                    'reference_id' => $this->serviceBill->reference,
                    'amount' => AmountData::fromArray([
                        'currency' => $this->serviceOrder->currency_code,
                        'total' => $this->serviceBill->total_amount,
                        'details' => PaymentDetailsData::fromArray(
                            [
                                'subtotal' => strval($this->serviceBill->sub_total),
                                'tax' => strval($this->serviceBill->tax_total),
                            ]
                        ),
                    ]),
                ]
            ),
            payment_driver: $this->paymentMethod->slug
        );

        $result = $this->createPaymentAction
            ->execute($this->serviceBill, $providerData);

        if ($result->success) {
            $this->createServiceTransaction();

            return $result;
        }

        throw new PaymentException();
    }

    /** @throws Throwable */
    private function createServiceTransaction(): void
    {
        $payment = Payment::wherePayableType(ServiceBill::class)
            ->wherePayableId($this->serviceBill->id)
            ->latest()
            ->first();

        if (is_null($payment)) {
            throw new ModelNotFoundException(trans('No payment transaction found'));
        }

        $this->createServiceTransactionAction->execute(
            new CreateServiceTransactionData(
                service_order: $this->serviceOrder,
                service_bill: $this->serviceBill,
                service_transaction_status: ServiceTransactionStatus::PENDING,
                payment: $payment
            )
        );
    }
}
