<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use App\FilamentTenant\Support\Contracts\HasProductOptions;
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
            $result[] = $current;

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

            return trans('Add :name', ['name' => $name]);
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

            if ( ! $activeProductOptionStatePath = $livewire->getActiveProductOptionItemStatePath()) {
                return;
            }

            $oldData = data_get($livewire, $activeProductOptionStatePath) ?? [];
            data_set($livewire, $activeProductOptionStatePath, array_merge($oldData, $data));
            data_set($livewire, 'data.product_variants', $productVariants);
            $livewire->unmountProductOptionItem();
        });
    }
}
