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

        foreach ($productData->images as $image) {
            if ($image instanceof UploadedFile && $imageString = $image->get()) {
                $product->addMediaFromString($imageString)
                    ->usingFileName($image->getClientOriginalName())
                    ->usingName(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME))
                    ->toMediaCollection('image');
            }
        }

        if ($productData->images === null) {
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
                'weight' => $productData->weight,
                'status' => $productData->status,
                'stock' => $productData->stock,
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
