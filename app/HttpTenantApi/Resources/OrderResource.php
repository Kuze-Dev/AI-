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
            'sub_total' => $this->sub_total,
            'discount_total' => $this->discount_total,
            'total' => $this->total,
            'payment_method' => $this->payment_method,
            'payment_details' => $this->payment_details,
            'shipping_address' => $this->shipping_address,
            'billing_address' => $this->billing_address,
        ];
    }

    public function toRelationships(Request $request): array
    {
        return [
            'order_lines' => fn () => OrderLineResource::collection($this->order_lines),
        ];
    }
}
