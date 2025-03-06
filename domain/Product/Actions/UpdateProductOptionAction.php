<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\DataTransferObjects\ProductOptionData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Support\Common\Actions\SyncMediaCollectionAction;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;

class UpdateProductOptionAction
{
    public function execute(Product $product, ProductData $productData): void
    {
        /** Flush Product Option */
        if (! filled($productData->product_options) && ! filled($productData->product_variants)) {
            ProductOption::whereProductId($product->id)->delete();

            return;
        }

        /** Process Update or Create of Product Options and Option Values */
        foreach ($productData->product_options ?? [] as $key => $productOption) {
            $productOptionModel = ProductOption::find($productOption->id);

            if ($productOptionModel instanceof ProductOption) {
                $productOptionModel->product_id = $product->id;
                $productOptionModel->name = $productOption->name;
                $productOptionModel->is_custom = $productOption->is_custom;
                $productOptionModel->save();

                // Update product variant
                $productData->product_variants = $this->searchAndChangeValueByName(
                    $productOption->name,
                    $productData->product_variants ?? [],
                    $productOption->id
                );
            } else {
                $newProductOptionModel = ProductOption::create([
                    'product_id' => $product->id,
                    'name' => $productOption->name,
                    'is_custom' => $productOption->is_custom,
                ]);

                // Update product variant
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
            }

            // Process Update or Create of Product Option Value
            foreach ($productOption->productOptionValues as $key2 => $productOptionValue) {
                $productOptionValue = $productOptionValue->withOptionId($productOption->id, $productOptionValue);
                $proxyOptionValueId = $productOptionValue->id;

                $optionValueModel = ProductOptionValue::find($productOptionValue->id);

                if ($optionValueModel instanceof ProductOptionValue) {
                    $optionValueModel->name = $productOptionValue->name;
                    $optionValueModel->product_option_id = $productOption->id;
                    /** @phpstan-ignore assign.propertyType */
                    $optionValueModel->data = ['icon_type' => $productOptionValue->icon_type, 'icon_value' => $productOptionValue->icon_value];
                    $optionValueModel->save();

                    // $this->uploadMediaMaterials(
                    //     $optionValueModel,
                    //     [['collection' => 'media', 'materials' => $productOptionValue->images]]
                    // );

                    $productData->product_variants = $this->searchAndChangeValueByName(
                        $productOptionValue->name,
                        $productData->product_variants ?? [],
                        $productOptionValue->id,
                        'option_value_id'
                    );
                } else {
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
                }

                $productOption->productOptionValues[$key2] = $productOptionValue;
            }

            $productData->product_options[$key] = $productOption;

            $this->sanitizeOptions($productOption);
        }

        $this->sanitizeOptions($productData, $product->id);
    }

    protected function sanitizeOptions(ProductData|ProductOptionData $dtoData, ?int $productId = null): void
    {
        $arrayForMapping = [];

        if ($dtoData instanceof ProductData && $productId) {
            $arrayForMapping = $dtoData->product_options;
        }

        if ($dtoData instanceof ProductOptionData) {
            $arrayForMapping = $dtoData->productOptionValues;
        }

        // Removal of Product Options
        $mappedIds = collect($arrayForMapping)
            ->pluck('id')
            ->toArray();

        if (count($mappedIds)) {
            if ($dtoData instanceof ProductData && $productId) {
                ProductOption::where('product_id', $productId)
                    ->whereNotIn('id', $mappedIds)
                    ->get()
                    ->each(function ($option) {
                        $option->delete();
                    });
            }

            if ($dtoData instanceof ProductOptionData) {
                ProductOptionValue::where('product_option_id', $dtoData->id)
                    ->whereNotIn('id', $mappedIds)
                    ->get()
                    ->each(function ($optionValue) {
                        $optionValue->delete();
                    });
            }
        }
    }

    protected function searchAndChangeValueByName(string $needle, array $haystack, int $newValue, string $field = 'option_id'): array
    {
        return collect($haystack)->map(function ($variant) use ($needle, $newValue, $field) {
            /** @var array<int, \Domain\Product\DataTransferObjects\VariantCombinationData> $variantCombination */
            $variantCombination = $variant->combination;
            $newCombinations = collect($variantCombination)->map(function ($combination) use ($needle, $newValue, $field) {
                if ($field === 'option_id' && strtolower($combination->option) === strtolower($needle)) {
                    return $combination->withOptionId($newValue, $combination);
                }

                if ($field === 'option_value_id' && strtolower($combination->option_value) === strtolower($needle)) {
                    return $combination->withOptionValueId($newValue, $combination);
                }

                return $combination;
            });

            return $variant->withCombination($newCombinations->toArray(), $variant);
        })->toArray();
    }

    protected function searchAndChangeValue(string|int $needle, array $haystack, int $newValue, string $field = 'option_id'): array
    {
        return collect($haystack)->map(function ($variant) use ($needle, $newValue, $field) {
            /** @var array<int, \Domain\Product\DataTransferObjects\VariantCombinationData> $variantCombination */
            $variantCombination = $variant->combination;
            $newCombinations = collect($variantCombination)->map(function ($combination) use ($needle, $newValue, $field) {
                if ($combination->{$field} === $needle) {
                    if ($field === 'option_id') {
                        return $combination->withOptionId($newValue, $combination);
                    }

                    if ($field === 'option_value_id') {
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
