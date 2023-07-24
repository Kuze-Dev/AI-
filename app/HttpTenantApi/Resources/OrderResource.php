<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Order\DataTransferObjects\OrderPaymentMethodData;
use Domain\PaymentMethod\Models\PaymentMethod;
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
            // 'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'created_at' => $this->created_at?->format('F d, Y H:i:s'),
            'tax_total' => $this->tax_total,
            'sub_total' => $this->sub_total,
            'discount_total' => $this->discount_total,
            'shipping_total' => $this->shipping_total,
            'total' => $this->total,
            'notes' => $this->notes,
            'shipping_method' => $this->shipping_method,
            'shipping_details' => $this->shipping_details,
            'is_paid' => $this->is_paid,
            'status' => $this->status,
            'shipping_address' => $this->shippingAddress,
            'billing_address' => $this->billingAddress,
        ];
    }

    public function toRelationships(Request $request): array
    {
        return [
            'orderLines' => fn () => OrderLineResource::collection($this->orderLines),
            'payments' => fn () => PaymentMethodOrderResource::make($this->payments->first()->paymentMethod),
        ];
    }
}
