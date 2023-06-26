<?php

namespace Domain\Support\Payments\Interfaces;

use Domain\Support\Payments\DataTransferObjects\PayPalProviderData;

interface HandlesManual
{
    public function handleManually(PayPalProviderData $payable);
}