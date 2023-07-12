<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use App\FilamentTenant\Support\Contracts\HasProductVariants;
use Filament\Forms\ComponentContainer;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Str;

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
        dd('not here yet #ProductVariantFormAction# ');

        // $this->modalHeading(function (HasProductVariants $livewire) {
        //     if ( ! $activeProductVariantStatePath = $livewire->getActiveProductVariantItemStatePath()) {
        //         return;
        //     }

        //     $state = data_get($livewire, $activeProductVariantStatePath);

        //     $productVariantComponent = $livewire->getProductVariantComponent();

        //     $name = (string) Str::of($productVariantComponent->getName())->headline()->singular();

        //     if ($state !== null) {
        //         return trans('Edit :label', ['label' => $productVariantComponent->getItemLabel($state) ?? $name]);
        //     }

        //     return trans('Manage :name', ['name' => $name]);
        // });

        // $this->slideOver(true);

        // $this->mountUsing(function (HasProductVariants $livewire, ComponentContainer $form) {
        //     if ( ! $activeProductVariantStatePath = $livewire->getActiveProductVariantItemStatePath()) {
        //         return;
        //     }

        //     $state = data_get($livewire, $activeProductVariantStatePath) ?? [];

        //     $form->fill($state);
        // });

        // $this->form(fn (HasProductVariants $livewire) => $livewire->getProductVariantFormSchema());

        // $this->action(function (HasProductVariants $livewire, array $data) {
        //     if ( ! $activeProductVariantStatePath = $livewire->getActiveProductVariantItemStatePath()) {
        //         return;
        //     }

        //     $oldData = data_get($livewire, $activeProductVariantStatePath) ?? [];
        //     data_set($livewire, $activeProductVariantStatePath, array_merge($oldData, $data));
        //     $livewire->unmountProductVariantItem();
        // });
    }
}
