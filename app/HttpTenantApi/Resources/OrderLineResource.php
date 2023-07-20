<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Order\Models\OrderLine
 */
class OrderLineResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        $reviews = $this->reviewDetails();

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
            'purchasable_data' => $this->purchasable_data,
            'review' => $reviews ? ReviewResource::make($reviews) : null,
            'remarks' => [
                'data' => $this->remarks_data,
                'media' => MediaResource::collection($this->media->filter(
                    fn ($media) =>
                    $media->collection_name === 'order_line_notes'
                )),
            ],
            'purchasable_media' => MediaResource::collection($this->media->filter(
                fn ($media) =>
                $media->collection_name === 'order_line_images'
            )),
        ];
    }
}
