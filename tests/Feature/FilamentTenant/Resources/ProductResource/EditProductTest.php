<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ProductResource\Pages\EditProduct;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Database\Factories\ProductOptionFactory;
use Domain\Product\Database\Factories\ProductOptionValueFactory;
use Domain\Product\Database\Factories\ProductVariantFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render product', function () {
    $product = ProductFactory::new()
        ->has(TaxonomyTermFactory::new()->for(TaxonomyFactory::new()->withDummyBlueprint())->count(2))
        ->has(ProductOptionFactory::new()->has(ProductOptionValueFactory::new()))
        ->has(ProductVariantFactory::new())
        ->has(MetaDataFactory::new())
        ->createOne();

    livewire(EditProduct::class, ['record' => $product->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $product->name,
            'images.0' => $product->getMedia()->first(),
            'product_variants.record-1' => $product->productVariants->toArray()[0],
            // 'product_options.record-1' => $product->productOptions->toArray()[0],
            'dimension' => $product->dimension,
            'sku' => $product->sku,
            'retail_price' => $product->retail_price,
            'selling_price' => $product->selling_price,
            'stock' => $product->stock,
        ])
        ->assertOk();
});
