<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use App\FilamentTenant\Support\Contracts\HasProductOptions;
use Domain\Product\Models\Product;
use Filament\Forms\ComponentContainer;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Str;

class ProductOptionFormAction extends Action
{
    protected string $view = 'filament.pages.actions.product-option-form-action';

    public static function getDefaultName(): ?string
    {
        return 'product-option-form';
    }

    private function generateCombinations($existingCombination, $options, $current = [], $index = 0, $result = [])
    {
        if ($index === count($options)) {
            $result[] = [
                'id' => uniqid(), // Add a unique ID
                'combination' => $current,
            ];

            return $result;
        }

        foreach ($options[$index]['productOptionValues'] as $value) {
            $newCurrent = $current;
            $newCurrent[$options[$index]['name']] = $value['name'];
            $result = $this->generateCombinations($existingCombination, $options, $newCurrent, $index + 1, $result);
        }

        return $result;
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
            if ( ! $activeProductOptionStatePath = $livewire->getActiveProductOptionItemStatePath()) {
                return;
            }

            $state = data_get($livewire, $activeProductOptionStatePath) ?? [];
            $form->fill($state);
        });

        $this->form(fn (HasProductOptions $livewire) => $livewire->getProductOptionFormSchema());

        $this->action(function (HasProductOptions $livewire, array $data) {
            $options = $data['options'];

            $existingCombination = $livewire->record?->productVariants->toArray() ?? [];

            $productVariants = $this->generateCombinations($existingCombination, $options);
            $updatedVariants = $this->updatingProductVariants($livewire->record, $productVariants);

            if ( ! $activeProductOptionStatePath = $livewire->getActiveProductOptionItemStatePath()) {
                return;
            }

            $oldData = data_get($livewire, $activeProductOptionStatePath) ?? [];
            foreach ($data['options'] as &$option) {
                if ( ! isset($option['id'])) {
                    $option['id'] = uniqid()/* generate or assign the desired value */;
                }
                if ( ! isset($option['slug'])) {
                    $option['slug'] = $option['name']/* generate or assign the desired value */;
                }

                foreach ($option['productOptionValues'] as &$value) {
                    if ( ! isset($value['id'])) {
                        $value['id'] = uniqid()/* generate or assign the desired value */;
                    }
                    if ( ! isset($value['slug'])) {
                        $value['slug'] = $value['name']/* generate or assign the desired value */;
                    }
                    if ( ! isset($value['product_option_id'])) {
                        $value['product_option_id'] = $option['id'];
                    }
                }
            }

            data_set($livewire, $activeProductOptionStatePath, array_merge($oldData, $data));
            data_set($livewire, 'data.product_options', array_merge($oldData, $data));
            data_set($livewire, 'data.product_variants', $updatedVariants);
            $livewire->unmountProductOptionItem();
        });
    }

    private function updatingProductVariants(Product $record, array $productVariants)
    {
        $existingCombination = $record->productVariants->toArray();
        $newCombination = $productVariants;
        $mergedCombination = [];

        foreach ($newCombination as $key => $item) {
            if (array_key_exists($key, $existingCombination)) {
                unset($item['id']);
                $mergedCombination[$key] = array_replace($existingCombination[$key], $item);
            } else {
                $mergedCombination[$key] = $item;
            }
        }
        $result = [];

        foreach ($mergedCombination as $key => $combination) {
            $keyData = serialize($combination['combination']);

            $combination['selling_price'] = isset($combination['selling_price']) ? $combination['selling_price'] : $record->selling_price;
            $combination['retail_price'] = isset($combination['retail_price']) ? $combination['retail_price'] : $record->retail_price;
            $combination['stock'] = isset($combination['stock']) ? $combination['stock'] : $record->stock;
            $combination['status'] = isset($combination['status']) ? $combination['status'] : $record->status;
            $combination['sku'] = isset($combination['sku']) ? $combination['sku'] : $record->sku . $key;
            unset($combination['product_id'], $combination['created_at'], $combination['updated_at']);

            $result[$keyData] = $combination;
        }

        $result = array_values($result);

        return $result;
    }
}
