<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;
use Support\MetaData\Actions\CreateMetaDataAction;

class CreateProductAction
{
    public function __construct(
        protected CreateMetaDataAction $createMetaData,
        protected CreateProductOptionAction $createProductOption,
        protected CreateOrUpdateProductVariantAction $createOrUpdateProductVariant,
        protected SyncMediaCollectionAction $syncMediaCollection,
    ) {
    }

    public function execute(ProductData $productData): Product
    {
        $product = Product::create($this->getProductAttributes($productData));

        $this->createMetaData->execute($product, $productData->meta_data);

        $this->createProductOption->execute($product, $productData);

        $this->createOrUpdateProductVariant->execute($product, $productData, false);

        $this->uploadMediaMaterials(
            $product,
            $productData->media_collection,
        );

        $product->taxonomyTerms()
            ->attach($productData->taxonomy_terms);

        return $product;
    }

    protected function uploadMediaMaterials(Product $product, array $mediaCollection): void
    {
        collect($mediaCollection)->each(function ($media) use ($product) {
            if (filled($media['materials'])) {
                /** @var array<int, array> $mediaMaterials */
                $mediaMaterials = $media['materials'];

                $mediaData = collect($mediaMaterials)->map(fn ($material) =>
                    /** @var \Illuminate\Http\UploadedFile|string $material */
                    new MediaData(media: $material))->toArray();

                $this->syncMediaCollection->execute($product, new MediaCollectionData(
                    collection: $media['collection'],
                    media: $mediaData,
                ));
            }
        });
    }

    protected function getProductAttributes(ProductData $productData): array
    {
        return [
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
        ];
    }
}
