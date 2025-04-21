<?php

declare(strict_types=1);

use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Database\Factories\ProductOptionFactory;
use Domain\Product\Database\Factories\ProductOptionValueFactory;
use Domain\Product\Database\Factories\ProductVariantFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Tier\Database\Factories\TierFactory;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Support\MetaData\Database\Factories\MetaDataFactory;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can list products', function () {
    ProductFactory::new(['status' => 1])
        ->has(TaxonomyTermFactory::new()
            ->for(TaxonomyFactory::new()
                ->withDummyBlueprint())
            ->count(2))
        ->count(10)->create();

    getJson('api/products')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 10)
                ->where('data.0.type', 'products')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});

it('can show a product', function () {
    $product = ProductFactory::new(['status' => 1])->createOne();

    getJson('api/products/'.$product->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($product) {
            $json
                ->where('data.type', 'products')
                ->where('data.id', $product->getRouteKey())
                ->where('data.attributes.name', $product->name)
                ->etc();
        });
});

it('can filter products', function (string $attribute) {
    $products = ProductFactory::new(['status' => 1])
        ->has(TaxonomyTermFactory::new()
            ->for(
                TaxonomyFactory::new()->withDummyBlueprint()
            )
            ->count(2))
        ->count(10)
        ->create();

    foreach ($products as $product) {
        getJson('api/products?'.http_build_query([
            'filter' => [$attribute => $product->$attribute],
        ]))
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($product) {
                $json
                    ->where('data.0.type', 'products')
                    ->where('data.0.id', $product->getRouteKey())
                    ->where('data.0.attributes.name', $product->name)
                    ->count('data', 1)
                    ->etc();
            });
    }
})->with([
    'name',
    'slug',
]);

it('can show a product with includes', function (string $include) {
    $product = ProductFactory::new(['name' => 'Foo', 'status' => 1])
        ->has(TaxonomyTermFactory::new()->for(TaxonomyFactory::new()->withDummyBlueprint())->count(2))
        ->has(ProductOptionFactory::new()->has(ProductOptionValueFactory::new()))
        ->has(TierFactory::new())
        ->has(ProductVariantFactory::new())
        ->has(MetaDataFactory::new())
        ->create();

    getJson("api/products/{$product->getRouteKey()}?".http_build_query(['include' => $include]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($product, $include) {
            $json
                ->where('data.type', 'products')
                ->where('data.id', Str::slug($product->name))
                ->where('data.attributes.name', $product->name)
                ->has(
                    'included',
                    callback: fn (AssertableJson $json) => $json->where('type', $include)->etc()
                )
                ->etc();
        });
})->with([
    'taxonomyTerms',
    'productOptions',
    'productVariants',
    'metaData',
    'tiers',
    'productTier',
]);

it('cant list inactive products', function () {
    ProductFactory::new(['status' => 0])
        ->count(5)
        ->create();

    getJson('api/products')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 0)
                ->etc();
        });
});

it('cant show an inactive product', function () {
    $product = ProductFactory::new(['name' => 'Foo', 'status' => 0])
        ->createOne();

    getJson("api/products/{$product->getRouteKey()}")->assertNotFound();
});
