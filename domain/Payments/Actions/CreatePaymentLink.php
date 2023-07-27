<?php

declare(strict_types=1);

namespace Domain\Payments\Actions;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\DataTransferObjects\CreatepaymentData;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\ProviderData;
use Domain\Payments\Models\Payment;

class CreatePaymentLink
{
    public function execute(Payment $payment, CreatepaymentData $paymentData): PaymentAuthorize
    {

        $paymentMethod = PaymentMethod::where('slug', $paymentData->payment_driver)->firstorFail();

        $providerData = new ProviderData(
            transactionData: $paymentData->transactionData,
            paymentModel: $payment,
            payment_method_id: $paymentMethod->id,
        );

        $result = app(PaymentManagerInterface::class)
            ->driver($paymentMethod->slug)
            ->withData($providerData)
            ->authorize();

        return $result;

    }
}
