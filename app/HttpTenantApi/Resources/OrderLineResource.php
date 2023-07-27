<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Order\DataTransferObjects\ProductOrderData;
use Domain\Order\DataTransferObjects\ProductVariantOrderData;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Order\Models\OrderLine
 */
class OrderLineResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return  [
            'purchasable_id' => $this->purchasable_id,
            'purchasable_sku' => $this->purchasable_sku,
            'name' => $this->name,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'tax_total' => $this->tax_total,
            'sub_total' => $this->sub_total,
            'discount_total' => $this->discount_total,
            'total' => $this->total,
            'reviewed_at' => $this->reviewed_at,
            'purchasable' => function () {
                // (WIP) DTO is my work around here becase the
                // purchable_data is an array coming from column
                if ( ! isset($this->purchasable_data['product'])) {
                    return ProductOrderData::fromArray($this->purchasable_data);
                } elseif (isset($this->purchasable_data['product'])) {
                    return ProductVariantOrderData::fromArray($this->purchasable_data);
                }
            },
            'remarks' => [
                'data' => $this->remarks_data,
                'media' => MediaResource::collection($this->media->filter(
                    fn ($media) => $media->collection_name === 'order_line_notes'
                )),
            ],
            'media' => MediaResource::collection($this->media->filter(
                fn ($media) => $media->collection_name === 'order_line_images'
            )),
        ];
    }
}
