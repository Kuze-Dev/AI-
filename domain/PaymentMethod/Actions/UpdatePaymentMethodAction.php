<?php

declare(strict_types=1);

namespace Domain\PaymentMethod\Actions;

use Domain\PaymentMethod\DataTransferObjects\PaymentMethodData;
use Domain\PaymentMethod\Models\PaymentMethod;

class UpdatePaymentMethodAction
{
    /**
     * Execute operations for updating
     * collection and save collection query.
     */
    public function execute(PaymentMethod $paymentMethod, PaymentMethodData $paymentMethodData): PaymentMethod
    {
        $paymentMethod->update([
            'title' => $paymentMethodData->title,
            'subtitle' => $paymentMethodData->subtitle,
            'gateway' => $paymentMethodData->gateway,
            'description' => $paymentMethodData->description,
            'status' => $paymentMethodData->status,
            'credentials' => $paymentMethodData->credentials,
        ]);

        return $paymentMethod;
    }
}
