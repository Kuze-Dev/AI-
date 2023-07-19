<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;

use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext();

    $customer = CustomerFactory::new()
        ->createOne();

    withHeader('Authorization', 'Bearer ' . $customer
        ->createToken('testing-auth')
        ->plainTextToken);
});

it('can add to cart', function () {
    $product = ProductFactory::new()
        ->createOne();

    postJson('api/carts/cartlines', [
        'purchasable_id' => $product->id,
        'purchasable_type' => "Product",
        "quantity" => 1
    ])
        ->assertValid()
        ->assertOk();
});
