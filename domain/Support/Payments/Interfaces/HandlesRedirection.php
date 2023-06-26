<?php

namespace Domain\Support\Payments\Interfaces;

use App\Models\Order\Order;
use App\Services\Payments\PaymentDetails;
use Domain\Support\Payments\DataTransferObjects\PayPalProviderData;
use Illuminate\Http\Request;

interface HandlesRedirection
{
    public function initRedirection( PayPalProviderData $providerData ): void;

    public function transactionId(): string;

    public function redirectUrl(): string;

    public function handleRedirectionCallback(Request $request, string $status);
}
