<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Support\MetaData\Actions\CreateMetaDataAction;
use Domain\Support\MetaData\Actions\UpdateMetaDataAction;
use Illuminate\Http\UploadedFile;

class UpdateProductAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
    ) {
    }

    public function execute(Product $product, ProductData $productData): Product
    {
        $product->update($this->getProductAttributes($productData));

        $product->metaData()->exists()
            ? $this->updateMetaData->execute($product, $productData->meta_data)
            : $this->createMetaData->execute($product, $productData->meta_data);

        if ($productData->image instanceof UploadedFile && $imageString = $productData->image->get()) {
            $product->addMediaFromString($imageString)
                ->usingFileName($productData->image->getClientOriginalName())
                ->usingName(pathinfo($productData->image->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection('image');
        }

        if ($productData->image === null) {
            $product->clearMediaCollection('image');
        }

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
                'shipping_fee' => $productData->shipping_fee,
                'status' => $productData->status,
                'stock' => $productData->stock,
                'is_digital_product' => $productData->is_digital_product,
                'is_featured' => $productData->is_featured,
                'is_special_offer' => $productData->is_special_offer,
                'allow_customer_remarks' => $productData->allow_customer_remarks,
                'allow_remark_with_image' => $productData->allow_remark_with_image,
            ],
            fn ($value) => filled($value)
        );
    }
}
