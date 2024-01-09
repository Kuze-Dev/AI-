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
use Domain\ServiceOrder\Exceptions\PaymentExceedLimitException;
use Domain\ServiceOrder\Exceptions\ServiceOrderFullyPaidException;
use Domain\ServiceOrder\Exceptions\ServiceOrderStatusStillPendingException;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LogicException;
use Throwable;

class CheckoutServiceOrderPartialPaymentAction
{
    public function __construct(
        private PaymentMethod $paymentMethod,
        private ServiceOrder $serviceOrder,
        private CreatePaymentAction $createPaymentAction,
        private CreateServiceTransactionAction $createServiceTransactionAction
    ) {
    }

    /** @throws Throwable */
    public function execute(CheckoutServiceOrderData $checkoutServiceOrderData): PaymentAuthorize
    {
        $this->paymentMethod = $this->preparePaymentMethod($checkoutServiceOrderData);

        $this->serviceOrder = $this->prepareServiceOrder($checkoutServiceOrderData);

        if (is_null($checkoutServiceOrderData->amount_to_pay)) {
            throw new LogicException(trans('No amount to pay found'));
        }

        return $this->proceedPayment($checkoutServiceOrderData->amount_to_pay);
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
    private function prepareServiceOrder(CheckoutServiceOrderData $checkoutServiceOrderData): ServiceOrder
    {
        $serviceOrder = ServiceOrder::whereReference($checkoutServiceOrderData->reference_id)
            ->first();

        if (! $serviceOrder?->is_partial_payment) {
            throw new ModelNotFoundException(trans('Please pay via service bill'));
        }

        if ($serviceOrder->totalBalance()->formatSimple() < $checkoutServiceOrderData->amount_to_pay) {
            throw new PaymentExceedLimitException('Amount to pay is higher than balance!');
        }

        if ($serviceOrder->totalBalance()->isZero()) {
            throw new ServiceOrderFullyPaidException('No current balance!');
        }

        return $serviceOrder;
    }

    /** @throws Throwable */
    private function proceedPayment(float $amountToPay): PaymentAuthorize
    {
        if ($this->serviceOrder->status === ServiceOrderStatus::PENDING) {
            throw new ServiceOrderStatusStillPendingException();
        }

        $providerData = new CreatepaymentData(
            transactionData: TransactionData::fromArray(
                [
                    'reference_id' => $this->serviceOrder->reference,
                    'amount' => AmountData::fromArray([
                        'currency' => $this->serviceOrder->currency_code,
                        'total' => $amountToPay,
                        'details' => PaymentDetailsData::fromArray(
                            [
                                // 'subtotal' => strval($this->serviceOrder->totalBalanceSubtotal()->formatSimple()),
                                // 'tax' => strval($this->serviceOrder->totalBalanceTax()->formatSimple()),
                            ]
                        ),
                    ]),
                ]
            ),
            payment_driver: $this->paymentMethod->slug
        );

        $result = $this->createPaymentAction
            ->execute($this->serviceOrder, $providerData);

        if ($result->success) {
            $this->createServiceTransaction($amountToPay);

            return $result;
        }

        throw new PaymentException($result->message ?? 'Payment Unauthorized');
    }

    /** @throws Throwable */
    private function createServiceTransaction(float $amountToPay): void
    {
        $payment = Payment::wherePayableType($this->serviceOrder->getTable())
            ->wherePayableId($this->serviceOrder->id)
            ->latest()
            ->first();

        if (is_null($payment)) {
            throw new ModelNotFoundException(trans('No payment transaction found'));
        }

        $this->createServiceTransactionAction->execute(
            new CreateServiceTransactionData(
                service_order: $this->serviceOrder,
                service_bill: null,
                total_amount: $amountToPay,
                service_transaction_status: ServiceTransactionStatus::PENDING,
                payment: $payment
            )
        );
    }
}
