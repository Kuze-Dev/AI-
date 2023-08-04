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
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'retail_price' => $this->retail_price,
            'selling_price' => $this->selling_price,
            'stock' => $this->stock,
            'status' => $this->status, // TODO: use enum, to clarify what is available as valid
            'is_digital_product' => $this->is_digital_product,
            'is_featured' => $this->is_featured,
            'is_favorite' => $this->isFavorite(), // TODO: do not make resource as getter
            'is_special_offer' => $this->is_special_offer,
            'allow_customer_remarks' => $this->allow_customer_remarks,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'media' => fn () => MediaResource::collection($this->media),
            'productOptions' => fn () => ProductOptionResource::collection($this->productOptions),
            'productVariants' => fn () => ProductVariantResource::collection($this->productVariants),
            'taxonomyTerms' => fn () => TaxonomyTermResource::collection($this->taxonomyTerms),
            'metaData' => fn () => MetaDataResource::make($this->metaData),
        ];
    }
}
