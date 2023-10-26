<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use App\FilamentTenant\Support\Contracts\HasProductVariants;
use Filament\Forms\ComponentContainer;
use Filament\Pages\Actions\Action;

class ProductVariantFormAction extends Action
{
    protected string $view = 'filament.pages.actions.product-variant-form-action';

    public static function getDefaultName(): ?string
    {
        return 'product-variant-form';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->modalHeading(function (HasProductVariants $livewire) {
            if (! $activeProductVariantStatePath = $livewire->getActiveProductVariantItemStatePath()) {
                return;
            }

            $state = data_get($livewire, $activeProductVariantStatePath);

            if ($state !== null) {
                return trans('Edit Variant');
            }

            return trans('Manage Variant');
        });

        $this->slideOver(true);

        $this->mountUsing(function (HasProductVariants $livewire, ComponentContainer $form) {
            if (! $activeProductVariantStatePath = $livewire->getActiveProductVariantItemStatePath()) {
                return;
            }

            $state = data_get($livewire, $activeProductVariantStatePath) ?? [];
            $form->fill($state);
        });

        $this->form(fn (HasProductVariants $livewire) => $livewire->getProductVariantFormSchema());

        $this->action(function (HasProductVariants $livewire, array $data) {
            if (! $activeProductVariantStatePath = $livewire->getActiveProductVariantItemStatePath()) {
                return;
            }

            $oldData = data_get($livewire, $activeProductVariantStatePath) ?? [];
            data_set($livewire, $activeProductVariantStatePath, array_merge($oldData, $data));
            $livewire->unmountProductVariantItem();
        });
    }
}
