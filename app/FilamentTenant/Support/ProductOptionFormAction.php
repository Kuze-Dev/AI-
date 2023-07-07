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

    private function generateCombinations($options, $current = [], $index = 0, $result = [])
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
            $result = $this->generateCombinations($options, $newCurrent, $index + 1, $result);
        }

        return $result;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->modalHeading(function (HasProductOptions $livewire) {
            if ( ! $activeProductOptionStatePath = $livewire->getActiveProductOptionItemStatePath()) {
                return;
            }

            $state = data_get($livewire, $activeProductOptionStatePath);

            $productOptionComponent = $livewire->getProductOptionComponent();

            $name = (string) Str::of($productOptionComponent->getName())->headline()->singular();

            if ($state !== null) {
                return trans('Edit :label', ['label' => $productOptionComponent->getItemLabel($state) ?? $name]);
            }

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

            $productVariants = $this->generateCombinations($options);

            $updatedVariants = $this->updatingProductVariants($livewire->record, $productVariants);

            if ( ! $activeProductOptionStatePath = $livewire->getActiveProductOptionItemStatePath()) {
                return;
            }

            $oldData = data_get($livewire, $activeProductOptionStatePath) ?? [];
            data_set($livewire, $activeProductOptionStatePath, array_merge($oldData, $data));
            data_set($livewire, 'data.product_variants', $updatedVariants);
            $livewire->unmountProductOptionItem();
        });
    }

    private function updatingProductVariants(Product $record, array $productVariants)
    {
        // dd($record->productVariants->toArray(), $productVariants);
        // $defaultSellingPrice = 2;

        $existingCombination = $record->productVariants->toArray();
        $newCombination = $productVariants;

        // $newCombination = [
        //     [
        //         'id' => 2313213,
        //         'combination' => [
        //             'size' => 'large',
        //             'color' => 'white'
        //         ],
        //     ],
        //     [
        //         'id' => 2313214,
        //         'combination' => [
        //             'size' => 'large',
        //             'color' => 'black'
        //         ],
        //     ],
        //     [
        //         'id' => 2313215,
        //         'combination' => [
        //             'size' => 'large',
        //             'color' => 'gray'
        //         ],
        //     ]
        // ];

        // $existingCombination = [
        //     [
        //         'id' => 1,
        //         'combination' => [
        //             'size' => 'large',
        //             'color' => 'white'
        //         ],
        //         'selling_price' => 4,
        //     ],
        //     [
        //         'id' => 2,
        //         'combination' => [
        //             'size' => 'large',
        //             'color' => 'black'
        //         ],
        //         'selling_price' => 5,
        //     ]
        // ];

        $mergedCombination = array_merge_recursive($newCombination, $existingCombination);
        $result = [];

        foreach ($mergedCombination as $key => $combination) {
            $keyData = serialize($combination['combination']);

            // $result[$keyData] = isset($combination['selling_price']) ? $combination : $combination['selling_price'];
            // if (isset($combination['selling_price'])) {
            //     $result[$keyData] = $combination;
            // } else {
            //     $combination['selling_price'] = $record->selling_price;
            //     $result[$keyData] = $combination;
            // }

            $combination['selling_price'] = isset($combination['selling_price']) ? $combination['selling_price'] : $record->selling_price;
            $combination['retail_price'] = isset($combination['retail_price']) ? $combination['retail_price'] : $record->retail_price;
            $combination['stock'] = isset($combination['stock']) ? $combination['stock'] : $record->stock;
            $combination['status'] = isset($combination['status']) ? $combination['status'] : $record->status;
            $combination['sku'] = isset($combination['sku']) ? $combination['sku'] : $record->sku . $key;
            unset($combination['product_id'], $combination['created_at'], $combination['updated_at']);

            $result[$keyData] = $combination;

        }

        $result = array_values($result);

        // print_r($result);
        // dd('resulttt : ', $result);
        return $result;
    }
}
