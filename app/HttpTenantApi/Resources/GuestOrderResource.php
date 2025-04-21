<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Order\Models\Order
 */
class GuestOrderResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'customer' => [
                'first_name' => $this->customer_first_name,
                'last_name' => $this->customer_last_name,
                'mobile' => $this->customer_mobile,
                'email' => $this->customer_email,
            ],
            'reference' => $this->reference,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'tax_percentage' => $this->tax_percentage,
            'tax_display' => $this->tax_display,
            'tax_total' => number_format((float) $this->tax_total, 2, '.', ','),
            'sub_total' => number_format((float) $this->sub_total, 2, '.', ','),
            'discount_code' => $this->discount_code,
            'discount_total' => number_format((float) $this->discount_total, 2, '.', ','),
            'shipping_total' => number_format((float) $this->shipping_total, 2, '.', ','),
            'total' => number_format((float) $this->total, 2, '.', ','),
            'notes' => $this->notes,
            'is_paid' => $this->is_paid,
            'shipping_address' => $this->shippingAddress,
            'billing_address' => $this->billingAddress,
            'cancelled_reason' => $this->cancelled_reason,
            'currency' => [
                'symbol' => $this->currency_symbol,
                'code' => $this->currency_code,
                'name' => $this->currency_name,
            ],
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'orderLines' => fn () => OrderLineResource::collection($this->orderLines),
            'payments' => fn () => PaymentOrderResource::make($this->payments->first()),
            'shippingMethod' => fn () => ShippingMethodResource::make($this->shippingMethod),
        ];
    }
}
