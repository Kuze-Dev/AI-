<?php

declare(strict_types=1);

use Domain\Review\Database\Factories\ReviewFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Order\Database\Factories\OrderFactory;
use Domain\Order\Enums\OrderStatuses;
use Domain\Product\Database\Factories\ProductFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext();
    $customer = CustomerFactory::new()
        ->createOne();

    withHeader('Authorization', 'Bearer ' . $customer
        ->createToken('testing-auth')
        ->plainTextToken);

    $this->customer = $customer;
});

it('can list reviews', function () {
    $review = ReviewFactory::new()
        ->createOne();
    $response = $this->getJson("api/reviews/{$review->product_id}");
    $response
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($review) {
            $json
                ->where('data.0.id', (string) $review->id)
                ->where('data.0.type', 'reviews')
                ->etc();
        });
});

it('can store review', function () {
    $product = ProductFactory::new()->createOne();
    $order = OrderFactory::new()->createOne(['status' => OrderStatuses::FULFILLED]);
    Sanctum::actingAs($this->customer);
    $response = $this->postJson('api/reviews', [
        'product_id' => $product->id,
        'rating' => 4,
        'comment' => 'sample comment',
        'order_line_id' => $order->orderLines->first()->id,
        'is_anonymous' => false

    ]);

    $response->assertStatus(201);
    $response->assertJson(['message' => 'Review item created successfully']);
});

it('can delete review', function () {
    $review = ReviewFactory::new()
    ->createOne();

    deleteJson('api/reviews/' . $review->product_id)
        ->assertValid();

    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});
