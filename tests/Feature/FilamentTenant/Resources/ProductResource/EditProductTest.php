<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ProductResource\Pages\EditProduct;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Database\Factories\ProductOptionFactory;
use Domain\Product\Database\Factories\ProductOptionValueFactory;
use Domain\Product\Database\Factories\ProductVariantFactory;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

// it('can render product', function () {
//     $product = ProductFactory::new()
//         // ->has(TaxonomyTermFactory::new()->for(TaxonomyFactory::new()->withDummyBlueprint())->count(2))
//         ->has(ProductOptionFactory::new()->has(ProductOptionValueFactory::new()->count(2)))
//         ->has(ProductVariantFactory::new()->count(2))
//         ->has(MetaDataFactory::new())
//         ->createOne();

//     livewire(EditProduct::class, ['record' => $product->getRouteKey()])
//         ->assertFormExists()
//         ->assertSuccessful()
//         ->assertFormSet([
//             'name' => $product->name,
//             'images.0' => $product->getMedia()->first(),
//             // 'product_variants.0' => $product->productVariants->toArray()[0],
//             'dimension' => $product->dimension,
//             'sku' => $product->sku,
//             'retail_price' => $product->retail_price,
//             'selling_price' => $product->selling_price,
//             'stock' => 50,
//         ])
//         ->assertOk();
// });
