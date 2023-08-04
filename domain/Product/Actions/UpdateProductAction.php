<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Media\Actions\CreateMediaAction;
use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Illuminate\Http\UploadedFile;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\MetaData\Actions\UpdateMetaDataAction;
use Illuminate\Support\Arr;

class UpdateProductAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
        protected UpdateProductOptionAction $updateProductOptionAction,
        protected CreateOrUpdateProductVariantAction $createOrUpdateProductVariantAction,
        protected CreateMediaAction $createMediaAction
    ) {
    }

    public function execute(Product $product, ProductData $productData): Product
    {
        $product->update($this->getProductAttributes($productData));

        $product->metaData()->exists()
            ? $this->updateMetaData->execute($product, $productData->meta_data)
            : $this->createMetaData->execute($product, $productData->meta_data);

        $this->updateProductOptionAction->execute($product, $productData);

        $this->createOrUpdateProductVariantAction->execute($product, $productData, false);

        if (filled($productData->images)) {
            $this->createMediaAction->execute($product, Arr::wrap($productData->images), 'image', false);
        }

        $this->createMediaAction->execute($product, Arr::wrap($productData->videos), 'video', false);

        $product->taxonomyTerms()->sync($productData->taxonomy_terms);

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
