<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Order\DataTransferObjects\PaymentMethodOrderData;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Payments\Models\Payment
 */
class PaymentOrderResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        /** @var \Domain\PaymentMethod\Models\PaymentMethod $paymentMethod */
        $paymentMethod = $this->paymentMethod;

        return [
            'gateway' => $this->gateway,
            'currency' => $this->currency,
            'amount' => $this->amount,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'customer_message' => $this->customer_message,
            'admin_message' => $this->admin_message,
            'media' => MediaResource::collection($this->media),
            'payment_method' => [
                'data' => PaymentMethodOrderData::fromPaymentMethod($paymentMethod),
                'media' => MediaResource::collection($paymentMethod->media),
            ],
        ];
    }
}
