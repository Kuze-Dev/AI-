<?php

declare(strict_types=1);

use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Database\Factories\ProductVariantFactory;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext();
    $customer = CustomerFactory::new()
        ->createOne();

    $cart = CartFactory::new()->setCustomerId($customer->id)->createOne();

    withHeader('Authorization', 'Bearer ' . $customer
        ->createToken('testing-auth')
        ->plainTextToken);

    $this->cart = $cart;

    return compact('cart');
});

it('can show customer cart', function () {
    // getJson('api/carts')
    //     ->assertValid()
    //     ->assertJson([
    //         'data' => [
    //             'id' => (string) $this->cart->id,
    //             'type' => 'carts',
    //             'attributes' => [
    //                 'id' => $this->cart->id,
    //                 'coupon_code' => null,
    //             ],
    //             'relationships' => [
    //                 'cartLines' => [
    //                     'data' => [],
    //                     'meta' => [],
    //                     'links' => [],
    //                 ],
    //             ],
    //             'meta' => [],
    //             'links' => [],
    //         ],
    //         'included' => [],
    //         'jsonapi' => [
    //             'version' => '1.0',
    //             'meta' => [],
    //         ],
    //     ]);
});

it('can delete cart', function () {
    deleteJson('api/carts/' . $this->cart->id)
        ->assertValid()
        ->assertNoContent();
});
