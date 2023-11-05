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
use Domain\ServiceOrder\DataTransferObjects\CheckoutServiceOrderData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Exceptions\ServiceOrderStatusStillPendingException;
use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class CheckoutServiceOrderAction
{
    public function __construct(private CreatePaymentAction $createPaymentAction)
    {
    }

    /** @throws Throwable */
    public function execute(CheckoutServiceOrderData $checkoutServiceOrderData): PaymentAuthorize
    {
        $paymentMethod = $this->preparePaymentMethod($checkoutServiceOrderData->payment_method);

        $serviceBill = $this->prepareServiceBill($checkoutServiceOrderData->reference_id);

        $payment = $this->proceedPayment($serviceBill, $paymentMethod);

        return $payment;
    }

    private function preparePaymentMethod(string $payment_method): PaymentMethod
    {
        $paymentMethod = PaymentMethod::whereSlug($payment_method)->first();

        if (! $paymentMethod instanceof PaymentMethod) {
            throw new ModelNotFoundException('No payment method found');
        }

        return $paymentMethod;
    }

    private function prepareServiceBill(string $reference_id): ServiceBill
    {
        $serviceBill = ServiceBill::whereReference($reference_id)->first();

        if (! $serviceBill instanceof ServiceBill) {
            throw new ModelNotFoundException('No service bill found');
        }

        return $serviceBill;
    }

    /** @throws Throwable */
    private function proceedPayment(
        ServiceBill $serviceBill,
        PaymentMethod $paymentMethod
    ): PaymentAuthorize {

        /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
        $serviceOrder = $serviceBill->serviceOrder;

        if ($serviceOrder->status === ServiceOrderStatus::PENDING) {
            throw new ServiceOrderStatusStillPendingException();
        }

        $providerData = new CreatepaymentData(
            transactionData: TransactionData::fromArray(
                [
                    'reference_id' => $serviceBill->reference,
                    'amount' => AmountData::fromArray([
                        'currency' => $serviceOrder->currency_code,
                        'total' => $serviceBill->total_amount,
                        'details' => PaymentDetailsData::fromArray(
                            [
                                'subtotal' => strval($serviceBill->sub_total),
                                'tax' => strval($serviceBill->tax_total),
                            ]
                        ),
                    ]),
                ]
            ),
            payment_driver: $paymentMethod->slug
        );

        $result = $this->createPaymentAction
            ->execute($serviceBill, $providerData);

        if ($result->success) {
            return $result;
        }

        throw new PaymentException();
    }
}
