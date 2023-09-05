<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Media\Actions\CreateMediaAction;
use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Support\MetaData\Actions\CreateMetaDataAction;
use Support\MetaData\Actions\UpdateMetaDataAction;
use Illuminate\Support\Arr;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;

class UpdateProductAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected UpdateMetaDataAction $updateMetaData,
        protected UpdateProductOptionAction $updateProductOption,
        protected CreateOrUpdateProductVariantAction $createOrUpdateProductVariant,
        protected CreateMediaAction $createMedia,
        protected SyncMediaCollectionAction $syncMediaCollection,
    ) {
    }

    /** @param \Domain\Product\Models\Product $product */
    public function execute(Product $product, ProductData $productData): Product
    {
        $product->update($this->getProductAttributes($productData));

        $product->metaData()->exists()
            ? $this->updateMetaData->execute($product, $productData->meta_data)
            : $this->createMetaData->execute($product, $productData->meta_data);

        $this->updateProductOption->execute($product, $productData);

        $this->createOrUpdateProductVariant->execute($product, $productData, false);

        if (filled($productData->images)) {
            $mediaData = [];
            foreach ($productData->images as $image) {
                $mediaData[] = new MediaData(media: $image);
            }

            $this->syncMediaCollection->execute($product, new MediaCollectionData(
                collection: 'image',
                media: $mediaData,
            ));

            // $this->createMedia->execute($product, Arr::wrap($productData->images), 'image', false);
        }

        if (filled($productData->videos)) {
            $mediaData = [];
            foreach ($productData->videos as $video) {
                $mediaData[] = new MediaData(media: $video);
            }
            
            $this->syncMediaCollection->execute($product, new MediaCollectionData(
                collection: 'video',
                media: $mediaData,
            ));
        }
        // $this->createMedia->execute($product, Arr::wrap($productData->videos), 'video', false);

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
                'allow_stocks' => $productData->allow_stocks,
                'dimension' => ['length' => $productData->length, 'width' => $productData->width, 'height' => $productData->height],
            ],
            fn ($value) => filled($value)
        );
    }
}
