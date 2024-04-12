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
use Domain\ServiceOrder\Exceptions\ServiceBillAlreadyPaidException;
use Domain\ServiceOrder\Exceptions\ServiceOrderStatusStillPendingException;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class CheckoutServiceOrderAction
{
    public function __construct(
        private PaymentMethod $paymentMethod,
        private ServiceBill $serviceBill,
        private ServiceOrder $serviceOrder,
        private readonly CreatePaymentAction $createPaymentAction,
        private readonly CreateServiceTransactionAction $createServiceTransactionAction
    ) {
    }

    /** @throws Throwable */
    public function execute(CheckoutServiceOrderData $checkoutServiceOrderData): PaymentAuthorize
    {
        $this->paymentMethod = $this->preparePaymentMethod($checkoutServiceOrderData);

        $this->serviceBill = $this->prepareServiceBill($checkoutServiceOrderData);

        $this->serviceOrder = $this->prepareServiceOrder();

        return $this->proceedPayment();
    }

    /** @throws Throwable */
    private function preparePaymentMethod(CheckoutServiceOrderData $checkoutServiceOrderData): PaymentMethod
    {
        $paymentMethod = PaymentMethod::whereSlug($checkoutServiceOrderData->payment_method)
            ->first();

        if (is_null($paymentMethod)) {
            throw new ModelNotFoundException(trans('No payment method found'));
        }

        return $paymentMethod;
    }

    /** @throws Throwable */
    private function prepareServiceBill(CheckoutServiceOrderData $checkoutServiceOrderData): ServiceBill
    {
        $serviceBill = ServiceBill::whereReference($checkoutServiceOrderData->reference_id)
            ->first();

        if (is_null($serviceBill)) {
            throw new ModelNotFoundException(trans('No service bill found'));
        }

        if ($serviceBill->is_paid) {
            throw new ServiceBillAlreadyPaidException();
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

        throw new PaymentException($result->message ?? 'Payment Unauthorized');
    }

    /** @throws Throwable */
    private function createServiceTransaction(): void
    {
        $payment = Payment::wherePayableType($this->serviceBill->getTable())
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
                total_amount: null,
                service_transaction_status: ServiceTransactionStatus::PENDING,
                payment: $payment
            )
        );
    }
}
