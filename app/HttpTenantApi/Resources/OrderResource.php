<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Order\Models\Order
 */
class OrderResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'reference' => $this->reference,
            'status' => $this->status,
            'created_at' => $this->created_at?->format('F d, Y H:i:s'),
            'tax_total' => $this->tax_total,
            'sub_total' => $this->sub_total,
            'discount_total' => $this->discount_total,
            'shipping_total' => $this->shipping_total,
            'total' => $this->total,
            'notes' => $this->notes,
            'is_paid' => $this->is_paid,
            'shipping_address' => $this->shippingAddress,
            'billing_address' => $this->billingAddress,
            'cancelled_reason' => $this->cancelled_reason,
            'currency' => [
                'symbol' => $this->currency_symbol,
                'code' => $this->currency_code,
                'name' => $this->currency_name,
            ]
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'orderLines' => fn () => OrderLineResource::collection($this->orderLines),
            'payments' => fn () => PaymentOrderResource::make($this->payments->first()),
        ];
    }
}
