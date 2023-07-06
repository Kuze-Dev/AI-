<?php

declare(strict_types=1);

namespace Domain\PaymentMethod\Actions;

use Domain\PaymentMethod\Models\PaymentMethod;

class DeletePaymentMethodAction
{
    /** Execute a delete collection query. */
    public function execute(PaymentMethod $paymentMethod): ?bool
    {
        return $paymentMethod->delete();
    }
}
