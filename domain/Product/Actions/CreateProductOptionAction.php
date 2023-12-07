<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;

class CreateProductOptionAction
{
    public function execute(Product $product, ProductData $productData): void
    {
        if (filled($productData->product_options)) {
            foreach ($productData->product_options ?? [] as $key => $productOption) {
                $newProductOptionModel = ProductOption::create([
                    'product_id' => $product->id,
                    'name' => $productOption->name,
                    'is_custom' => $productOption->is_custom,
                ]);

                $productData->product_variants = $this->searchAndChangeValue(
                    $productOption->id,
                    $productData->product_variants ?? [],
                    $newProductOptionModel->id
                );

                $productOption = $productOption
                    ->withId(
                        $newProductOptionModel->id,
                        $productOption
                    );

                foreach ($productOption->productOptionValues as $key2 => $productOptionValue) {
                    $productOptionValue = $productOptionValue->withOptionId($productOption->id, $productOptionValue);
                    $proxyOptionValueId = $productOptionValue->id;

                    $newOptionValueModel = ProductOptionValue::create([
                        'name' => $productOptionValue->name,
                        'product_option_id' => $productOption->id,
                        'data' => ['icon_type' => $productOptionValue->icon_type, 'icon_value' => $productOptionValue->icon_value],
                    ]);

                    // $this->uploadMediaMaterials(
                    //     $newOptionValueModel,
                    //     [['collection' => 'media', 'materials' => $productOptionValue->images]]
                    // );

                    $productOptionValue = $productOptionValue
                        ->withId($newOptionValueModel->id, $productOptionValue);

                    $productData->product_variants = $this->searchAndChangeValue(
                        $proxyOptionValueId,
                        $productData->product_variants ?? [],
                        $newOptionValueModel->id,
                        'option_value_id'
                    );

                    $productOption->productOptionValues[$key2] = $productOptionValue;
                }

                $productData->product_options[$key] = $productOption;
            }
        }
    }

    protected function searchAndChangeValue(string|int $needle, array $haystack, int $newValue, string $field = 'option_id'): array
    {
        return collect($haystack)->map(function ($variant) use ($needle, $newValue, $field) {
            /** @var array<int, \Domain\Product\DataTransferObjects\VariantCombinationData> $variantCombination */
            $variantCombination = $variant->combination;
            $newCombinations = collect($variantCombination)->map(function ($combination) use ($needle, $newValue, $field) {
                if ($combination->{$field} == $needle) {
                    if ($field == 'option_id') {
                        return $combination->withOptionId($newValue, $combination);
                    }

                    if ($field == 'option_value_id') {
                        return $combination->withOptionValueId($newValue, $combination);
                    }
                }

                return $combination;
            });

            return $variant->withCombination($newCombinations->toArray(), $variant);
        })->toArray();
    }

    // protected function uploadMediaMaterials(ProductOptionValue $productOptionValue, array $mediaCollection): void
    // {
    //     collect($mediaCollection)->each(function ($media, $key) use ($productOptionValue) {
    //         /** @var array<int, array> $mediaMaterials */
    //         $mediaMaterials = $media['materials'];

    //         $mediaData = collect($mediaMaterials)->map(function ($material) {
    //             /** @var \Illuminate\Http\UploadedFile|string $material */
    //             return new MediaData(media: $material);
    //         })->toArray();

    //         $syncMediaCollection = new SyncMediaCollectionAction();

    //         $syncMediaCollection->execute($productOptionValue, new MediaCollectionData(
    //             collection: $media['collection'],
    //             media: $mediaData,
    //         ));
    //     });
    // }
}
