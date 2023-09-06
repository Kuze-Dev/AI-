<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\DataTransferObjects\ProductOptionData;
use Domain\Product\DataTransferObjects\ProductOptionValueData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Illuminate\Database\Eloquent\Collection;

class UpdateProductOptionAction
{
    public function execute(Product $product, ProductData $productData): array
    {
        $mutableVariants = $productData->product_variants ?? [];
        $mutableOptions = $productData->product_options;

        // Flush Product Option
        if ( ! filled($mutableOptions) && ! filled($mutableVariants)) {
            ProductOption::whereProductId($product->id)->delete();

            return [];
        }

        collect($mutableOptions)->map(function (ProductOptionData $productOption, int $key) use ($product, $mutableVariants) {
            $optionModel = ProductOption::find($productOption->id);

            if ($optionModel instanceof ProductOption) {
                $this->updateOption($product, $optionModel, $productOption);
            } else {
                $newOptionModel = $this->createOption($product, $productOption);

                $mutableVariants = $this->searchAndChangeValue($productOption->id, $mutableVariants, $newOptionModel->id);

                $productOption = $productOption->withId($newOptionModel->id, $productOption);
            }

            $mutableOptionValues = $productOption->productOptionValues;

            $collectedOptionValues = collect($mutableOptionValues)
                ->map(function ($optionValue, $key2) use ($productOption, $mutableVariants) {
                    $optionValue = $optionValue->withOptionId($productOption->id, $optionValue);
                    $proxyOptionValueId = $optionValue->id;

                    $optionValueModel = ProductOptionValue::find($optionValue->id);

                    $newOptionValueModel = $this->createOrUpdateOptionValue($optionValueModel, $productOption, $optionValue);

                    if ( ! $optionValueModel instanceof ProductOptionValue) {
                        $optionValue = $optionValue->withId($newOptionValueModel->id, $optionValue);

                        $mutableVariants = $this->searchAndChangeValue(
                            $proxyOptionValueId,
                            $mutableVariants,
                            $newOptionValueModel->id,
                            'option_value_id'
                        );
                    }

                    return $optionValue;
                })->toArray();

            $productOption = $productOption->withProductOptionValues($collectedOptionValues, $productOption);

            $mutableOptions[$key] = $productOption;

            $this->sanitizeOptions($productOption);
        });

        $this->sanitizeOptions($productData, $product->id);

        return $mutableVariants;
    }

    protected function createOrUpdateOptionValue(
        ProductOptionValue|Collection $optionValueModel,
        ProductOptionData $productOption,
        ProductOptionValueData $optionValue
    ): ProductOptionValue {

        if ($optionValueModel instanceof ProductOptionValue) {
            $optionValueModel->name = $optionValue->name;
            $optionValueModel->product_option_id = $productOption->id;

            $optionValueModel->save();
        } else {
            $optionValueModel = ProductOptionValue::create([
                'name' => $optionValue->name,
                'product_option_id' => $productOption->id,
            ]);
        }

        return $optionValueModel;
    }

    protected function createOption(Product $product, ProductOptionData $productOption): ProductOption
    {
        return ProductOption::create([
            'product_id' => $product->id,
            'name' => $productOption->name,
        ]);
    }

    protected function updateOption(
        Product $product,
        ProductOption $optionModel,
        ProductOptionData $productOption
    ): ProductOption {
        $optionModel->product_id = $product->id;
        $optionModel->name = $productOption->name;
        $optionModel->save();

        return $optionModel;
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

    protected function searchAndChangeValue(
        string|int $needle,
        array $haystack,
        int $newValue,
        string $field = 'option_id'
    ): array {
        return collect($haystack)->map(function ($variant) use ($needle, $newValue, $field) {
            /** @var array<int, \Domain\Product\DataTransferObjects\VariantCombinationData> $variantCombination */
            $variantCombination = $variant->combination;

            $newCombinations = collect($variantCombination)
                ->map(function ($combination) use ($needle, $newValue, $field) {
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
}
