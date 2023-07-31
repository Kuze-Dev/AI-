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
    public function toAttributes(Request $request): array
    {
        return  [
            'gateway' => $this->gateway,
            'currency' => $this->currency,
            'amount' => $this->amount,
            'status' => $this->status,
            'remarks' => $this->remarks,
            'message' => $this->message,
            'media' => MediaResource::collection($this->media),
            'payment_method' => [
                'data' => PaymentMethodOrderData::fromPaymentMethod($this->paymentMethod),
                'media' => MediaResource::collection($this->paymentMethod->media),
            ],
        ];
    }
}
