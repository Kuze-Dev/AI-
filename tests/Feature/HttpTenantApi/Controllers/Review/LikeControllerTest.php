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

it('can update review likes', function () {
    $review = ReviewFactory::new()->createOne();
    Sanctum::actingAs($this->customer);

    $response = $this->patchJson("api/reviews/like/{$review->id}");
    $response->assertOk(); 

    $this->assertTrue($review->review_likes()->where('customer_id', $review->customer_id)->exists());

    $response = $this->patchJson("api/reviews/like/{$review->id}");
    $response->assertOk(); 

    $this->assertFalse($review->review_likes()->where('customer_id', $review->customer_id)->exists());
});

