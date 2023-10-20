<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Favorite\Database\Factories\FavoriteFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext();
    $customer = CustomerFactory::new()
        ->createOne();

    $product = ProductFactory::new()
        ->createOne();

    $favorite = FavoriteFactory::new()->setCustomerId($customer->id)->setProductId($product->id)->createOne();

    withHeader('Authorization', 'Bearer '.$customer
        ->createToken('testing-auth')
        ->plainTextToken);

    $this->favorite = $favorite;
    $this->customer = $customer;
    $this->product = $product;
});

it('can show favorite with includes', function (string $include) {
    $favorite = $this->favorite;
    $product = $this->product;
    // $response =  getJson('api/favorites?' . http_build_query(['include' => $include]));
    // dd($response->json());

    getJson('api/favorites?'.http_build_query(['include' => $include]))
        ->assertValid()
        ->assertJson(function (AssertableJson $json) use ($favorite, $product) {
            $json
                ->where('data.0.type', 'favorites')
                ->where('data.0.id', (string) $favorite->id)
                ->has('included', 1)
                ->has(
                    'included',
                    callback: function (AssertableJson $json) use ($product) {
                        $json->where('type', 'products')
                            ->where('id', $product->slug)
                            ->has('attributes')
                            ->etc();
                    }
                )
                ->etc();
        })
        ->assertOk();
})->with(['product']);

it('can store favorite', function () {
    $customer = CustomerFactory::new()->createOne();
    $product = ProductFactory::new()->createOne();

    $response = $this->postJson('api/favorites', [
        'customer_id' => $customer->id,
        'product_id' => $product->id,
    ]);

    $response->assertStatus(201);
    $response->assertJson(['message' => 'Favorite item created successfully']);
});

it('can delete favorite', function () {
    deleteJson('api/favorites/'.$this->favorite->product_id)
        ->assertValid();

    $this->assertDatabaseMissing('favorites', ['id' => $this->favorite->id]);
});
