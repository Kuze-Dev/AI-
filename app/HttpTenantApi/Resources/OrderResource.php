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
            'id' => $this->id,
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
            'payment_method' => $this->payment_method,
            'payment_details' => $this->payment_details,
            'payment_status' => $this->payment_status,
            'payment_message' => $this->payment_message,
            'is_paid' => $this->is_paid,
            'status' => $this->status,
            'shipping_address' => $this->shippingAddress,
            'billing_address' => $this->billingAddress,
            'bank_proof_images' => $this->getMedia('bank_proof_images')->toArray(),
        ];
    }

    public function toRelationships(Request $request): array
    {
        return [
            'orderLines' => fn () => OrderLineResource::collection($this->orderLines),
        ];
    }
}
