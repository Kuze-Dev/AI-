<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Product\Models\Product
 */
class ProductResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'retail_price' => number_format((float) $this->retail_price, 2, '.', ','),
            'selling_price' => number_format((float) $this->selling_price, 2, '.', ','),
            'stock' => $this->stock,
            'status' => $this->status,
            'is_digital_product' => $this->is_digital_product,
            'is_featured' => $this->is_featured,
            'is_special_offer' => $this->is_special_offer,
            'allow_customer_remarks' => $this->allow_customer_remarks,
            'allow_guest_purchase' => $this->allow_guest_purchase,
            'allow_stocks' => $this->allow_stocks,
            'total_sold' => isset($this->total_sold) ? (int) $this->total_sold : null,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'media' => fn () => MediaResource::collection($this->media),
            'productOptions' => fn () => ProductOptionResource::collection($this->productOptions),
            'productVariants' => fn () => ProductVariantResource::collection($this->productVariants),
            'taxonomyTerms' => fn () => TaxonomyTermResource::collection($this->taxonomyTerms),
            'tiers' => fn () => TierResource::collection($this->tiers),
            'productTier' => fn () => ProductTierDiscountResource::collection($this->productTier),
            'metaData' => fn () => MetaDataResource::make($this->metaData),
        ];
    }
}
