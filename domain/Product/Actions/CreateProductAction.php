<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Media\Actions\CreateMediaAction;
use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;
use Illuminate\Support\Arr;

class CreateProductAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaTags,
        protected CreateOrUpdateRouteUrlAction $createOrUpdateRouteUrl,
        protected CreateProductOptionAction $createProductOptionAction,
        protected CreateProductVariantAction $createProductVariantAction,
        protected CreateMediaAction $createMediaAction,
    ) {
    }

    public function execute(ProductData $productData): Product
    {
        $product = Product::create($this->getProductAttributes($productData));

        $this->createMetaTags->execute($product, $productData->meta_data);

        $this->createProductOptionAction->execute($product, $productData);

        $this->createProductVariantAction->execute($product, $productData);

        if (filled($productData->images)) {
            $this->createMediaAction->execute($product, Arr::wrap($productData->images), 'image');
        }

        $product->taxonomyTerms()
            ->attach($productData->taxonomy_terms);

        return $product;
    }

    protected function getProductAttributes(ProductData $productData): array
    {
        return array_filter(
            [
                'name' => $productData->name,
                'sku' => $productData->sku,
                'description' => $productData->description,
                'retail_price' => $productData->retail_price,
                'selling_price' => $productData->selling_price,
                'weight' => $productData->weight,
                'status' => $productData->status,
                'stock' => $productData->stock,
                'minimum_order_quantity' => $productData->minimum_order_quantity,
                'is_digital_product' => $productData->is_digital_product,
                'is_featured' => $productData->is_featured,
                'is_special_offer' => $productData->is_special_offer,
                'allow_customer_remarks' => $productData->allow_customer_remarks,
                'dimension' => ['length' => $productData->length, 'width' => $productData->width, 'height' => $productData->height],
            ],
            fn ($value) => filled($value)
        );
    }
}
