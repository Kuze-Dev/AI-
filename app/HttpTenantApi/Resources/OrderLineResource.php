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
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'purchasable_id' => $this->purchasable_id,
            'purchasable_sku' => $this->purchasable_sku,
            'name' => $this->name,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'tax_total' => number_format((float) $this->tax_total, 2, '.', ','),
            'sub_total' => number_format((float) $this->sub_total, 2, '.', ','),
            'discount_total' => number_format((float) $this->discount_total, 2, '.', ','),
            'total' => number_format((float) $this->total, 2, '.', ','),
            'reviewed_at' => $this->reviewed_at,
            'purchasable' => function () {
                if (! isset($this->purchasable_data['product'])) {
                    /** @var array */
                    $productArray = $this->purchasable_data;

                    return ProductOrderData::fromArray($productArray);
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
            'reviews' => $this->review,
            'media' => MediaResource::collection($this->media->filter(
                fn ($media) => $media->collection_name === 'order_line_images'
            )),
        ];
    }
}
