<?php

declare(strict_types=1);

namespace Domain\Payments\Interfaces;

use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentCapture;
use Illuminate\Http\Request;

interface HandlesWebhook
{
    public function handleWebhook(Request $request, string $status): PaymentCapture;
}
