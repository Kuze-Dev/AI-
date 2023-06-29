<?php

declare(strict_types=1);

namespace Domain\PaymentMethod\Actions;

use Domain\PaymentMethod\DataTransferObjects\PaymentMethodData;
use Domain\PaymentMethod\Models\PaymentMethod;

class CreatePaymentMethodAction
{
    /** Execute create collection query. */
    public function execute(PaymentMethodData $paymentMethodData): PaymentMethod
    {
        $paymentMethod = PaymentMethod::create([
            'title' => $paymentMethodData->title,
            'subtitle' => $paymentMethodData->subtitle,
            'gateway' => $paymentMethodData->gateway,
            'description' => $paymentMethodData->description,
            'status' => $paymentMethodData->status,
        ]);

        return $paymentMethod;
    }
}
