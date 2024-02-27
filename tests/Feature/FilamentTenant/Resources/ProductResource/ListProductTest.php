<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ProductResource\Pages\ListProducts;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Database\Factories\ProductOptionFactory;
use Domain\Product\Database\Factories\ProductOptionValueFactory;
use Domain\Product\Database\Factories\ProductVariantFactory;
use Filament\Pages\Actions\DeleteAction;
use Support\MetaData\Database\Factories\MetaDataFactory;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can render product', function () {
    livewire(ListProducts::class)
        ->assertOk();
});

it('can list pages', function () {
    $products = ProductFactory::new()
        ->count(5)
        ->create();

    livewire(ListProducts::class)
        ->assertCanSeeTableRecords($products)
        ->assertOk();
});

it('can delete product', function () {
    $product = ProductFactory::new()
        // ->has(TaxonomyTermFactory::new()->for(TaxonomyFactory::new()->withDummyBlueprint())->count(2))
        ->has(ProductOptionFactory::new()->has(ProductOptionValueFactory::new()->count(2)))
        ->has(ProductVariantFactory::new()->count(2))
        ->has(MetaDataFactory::new())
        ->createOne();

    $productVariant = $product->productVariants->first();
    $productOption = $product->productOptions->first();
    $metaData = $product->metaData;

    livewire(ListProducts::class)
        ->callTableAction(DeleteAction::class, $product)
        ->assertOk();

    assertModelMissing($product);
    assertModelMissing($productVariant);
    assertModelMissing($productOption);
    assertModelMissing($metaData);
});
