<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\MetaData\Actions\UpdateMetaDataAction;

class UpdateProductActionV2
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
        protected UpdateProductOptionAction $updateProductOption,
        protected CreateOrUpdateProductVariantAction $createOrUpdateProductVariant,
        protected SyncMediaCollectionAction $syncMediaCollection,
    ) {
    }

    public function execute(Product $product, ProductData $productData): Product
    {
        $product->update($this->getProductAttributes($productData));

        $product->metaData()->exists()
            ? $this->updateMetaData->execute($product, $productData->meta_data)
            : $this->createMetaData->execute($product, $productData->meta_data);

        if (isset($productData->media_collection)) {
            $this->uploadMediaMaterials(
                $product,
                $productData->media_collection,
            );
        }

        if ($productData->taxonomy_terms) {
            $product->taxonomyTerms()->sync($productData->taxonomy_terms);
        }

        return $product;
    }

    protected function uploadMediaMaterials(Product $product, array $mediaCollection): void
    {
        collect($mediaCollection)->each(function ($media, $key) use ($product) {
            /** @var array<int, array> $mediaMaterials */
            $mediaMaterials = $media['materials'];

            $mediaData = collect($mediaMaterials)->map(function ($material) {
                /** @var \Illuminate\Http\UploadedFile|string $material */
                return new MediaData(media: $material);
            })->toArray();

            $this->syncMediaCollection->execute($product, new MediaCollectionData(
                collection: $media['collection'],
                media: $mediaData,
            ));
        });
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
                'allow_stocks' => $productData->allow_stocks,
                'allow_guest_purchase' => $productData->allow_guest_purchase,
                'dimension' => ['length' => $productData->length, 'width' => $productData->width, 'height' => $productData->height],
            ],
            fn ($value) => filled($value)
        );
    }
}
