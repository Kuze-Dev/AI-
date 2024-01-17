<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use App\FilamentTenant\Support\Contracts\HasProductOptions;
use Domain\Product\Models\Product;
use Filament\Forms\ComponentContainer;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductOptionFormAction extends Action
{
    protected string $view = 'filament.pages.actions.product-option-form-action';

    public static function getDefaultName(): ?string
    {
        return 'product-option-form';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->modalHeading(function (HasProductOptions $livewire) {
            $productOptionComponent = $livewire->getProductOptionComponent();

            $name = (string) Str::of($productOptionComponent->getName())->headline()->singular();

            return trans('Manage :name', ['name' => $name]);
        });

        $this->slideOver(true);

        $this->mountUsing(function (HasProductOptions $livewire, ComponentContainer $form) {
            if (! $activeProductOptionStatePath = $livewire->getActiveProductOptionItemStatePath()) {
                return;
            }

            $state = data_get($livewire, $activeProductOptionStatePath) ?? [];
            $form->fill($state);
        });

        $this->form(fn (HasProductOptions $livewire) => $livewire->getProductOptionFormSchema());

        $this->action(function (HasProductOptions $livewire, array $data) {
            $optionsWithProxies = $this->assignProxiesToProductOption($data['options']);
            $data['options'] = $optionsWithProxies;

            $productVariants = $this->generateCombinations($optionsWithProxies);
            /** @phpstan-ignore-next-line  */
            $updatedVariants = $this->updatingProductVariants($livewire->record, $productVariants);

            if (! $activeProductOptionStatePath = $livewire->getActiveProductOptionItemStatePath()) {
                return;
            }

            $oldData = data_get($livewire, $activeProductOptionStatePath) ?? [];

            data_set($livewire, $activeProductOptionStatePath, array_merge($oldData, $data));
            data_set($livewire, 'data.product_options', array_merge($oldData, $data));
            data_set($livewire, 'data.product_variants', $updatedVariants);
            $livewire->unmountProductOptionItem();
        });
    }

    protected function assignProxiesToProductOption(array $options): array
    {
        $optionsCollection = collect($options);

        $optionsCollection = $optionsCollection->map(function ($option) {
            $option['id'] = uniqid();
            $option['slug'] = $option['name'];

            $productOptionValues = $option['productOptionValues'];

            /** @var array<int, array> $productOptionValues */
            $option['productOptionValues'] = collect($productOptionValues)->map(function ($value) use ($option) {
                $value['id'] = uniqid();
                $value['slug'] = $value['name'];
                $value['product_option_id'] = $option['id'];
                $value['icon_type'] = $value['icon_type'];

                return $value;
            })->toArray();

            return $option;
        });

        return $optionsCollection->all();
    }

    protected function generateCombinations(array $inputArray): array
    {
        $outputArray = [];
        $firstOptionValues = $inputArray[0]['productOptionValues'] ?? [];
        $secondOptionValues = $inputArray[1]['productOptionValues'] ?? [];

        /** @var array<int, array> $firstOptionValues */
        $option1Values = collect($firstOptionValues);

        /** @var array<int, array> $secondOptionValues */
        $option2Values = collect($secondOptionValues);

        $option1Values->each(function ($optionValue1) use (&$outputArray, $inputArray, $option2Values) {
            if ($option2Values->isNotEmpty()) {
                $option2Values->each(function ($optionValue2) use (&$outputArray, $inputArray, $optionValue1) {
                    $combination = [
                        [
                            'option' => $inputArray[0]['name'],
                            'option_id' => $optionValue1['product_option_id'],
                            'option_value' => $optionValue1['name'],
                            'option_value_id' => $optionValue1['id'],
                        ],
                        [
                            'option' => $inputArray[1]['name'],
                            'option_id' => $optionValue2['product_option_id'],
                            'option_value' => $optionValue2['name'],
                            'option_value_id' => $optionValue2['id'],
                        ],
                    ];

                    $outputArray[] = [
                        'combination' => $combination,
                        'id' => uniqid(),
                    ];
                });
            } else {
                $combination = [
                    [
                        'option' => $inputArray[0]['name'],
                        'option_id' => $optionValue1['product_option_id'],
                        'option_value' => $optionValue1['name'],
                        'option_value_id' => $optionValue1['id'],
                    ],
                ];

                $outputArray[] = [
                    'combination' => $combination,
                    'id' => uniqid(),
                ];
            }
        });

        return $outputArray;
    }

    protected function hasMatchingCombinationViaName(array $combination1, array $combination2): bool
    {
        $collection1 = collect($combination1);
        $collection2 = collect($combination2);

        return $collection1->every(function ($option1) use ($collection2) {
            return $collection2->contains(function ($option2) use ($option1) {
                return strtolower($option1['option']) === strtolower($option2['option']) &&
                    strtolower($option1['option_value']) === strtolower($option2['option_value']);
            });
        });
    }

    protected function hasMatchingCombination(array $combination1, array $combination2): bool
    {
        $collection1 = collect($combination1);
        $collection2 = collect($combination2);

        return $collection1->every(function ($option1) use ($collection2) {
            return $collection2->contains(function ($option2) use ($option1) {
                return $option1['option_id'] === $option2['option_id'] &&
                    $option1['option_value_id'] === $option2['option_value_id'];
            });
        });
    }

    protected function updatingProductVariants(Product $record, array $productVariants): array
    {
        $mergedCombination = [];
        $result = [];
        $existingProductVariants = $record->productVariants->toArray();
        // Merge new and existing combinations
        $mergedCombination = $this->mergeCombinations($record->productVariants->toArray(), $productVariants);

        $result = collect($mergedCombination)->map(function ($combination) use ($existingProductVariants, $record, $result) {
            $generatedSku = $this->generateUniqueSku($record, collect($result));

            $matchingItem = collect($existingProductVariants)->first(function ($existingVariant) use ($combination) {
                return $this->hasMatchingCombinationViaName($combination['combination'], $existingVariant['combination']);
            });

            if (! is_null($matchingItem)) {
                return array_merge([
                    'sku' => $matchingItem['sku'],
                    'selling_price' => $matchingItem['selling_price'],
                    'retail_price' => $matchingItem['retail_price'],
                    'stock' => $matchingItem['stock'],
                    'status' => $matchingItem['status'],
                ], $combination);
            } else {
                return array_merge([
                    'sku' => $generatedSku,
                    'selling_price' => $record->selling_price,
                    'retail_price' => $record->retail_price,
                    'stock' => $record->stock ?? null,
                    'status' => $record->status,
                ], $combination);
            }
        })->values()->all();

        return array_values($result);
    }

    protected function mergeCombinations(array $existingCombination, array $newCombination): array
    {
        return collect($newCombination)->map(function ($item1) use ($existingCombination) {
            $matchingItem = collect($existingCombination)->first(function ($item2) use ($item1) {
                return $this->hasMatchingCombination($item1['combination'], $item2['combination']);
            });

            if ($matchingItem !== null) {
                return array_replace($matchingItem, $item1);
            } else {
                return $item1;
            }
        })->all();
    }

    /** @phpstan-ignore-next-line */
    protected function generateUniqueSku(Product $record, Collection $result): string
    {
        do {
            $generatedSku = $record->sku.rand(100000, 999999);
        } while ($result->pluck('sku')->contains($generatedSku));

        return $generatedSku;
    }
}
