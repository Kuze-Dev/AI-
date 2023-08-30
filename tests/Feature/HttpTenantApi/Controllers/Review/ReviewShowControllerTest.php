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

it('can list reviews ratings', function () {
    $product_id = 1;

    $reviewCount = 3;
    $reviews = ReviewFactory::new()
        ->count($reviewCount)
        ->create(['product_id' => $product_id]);

    $totalRatings = 0;
    $ratingCounts = [];

    foreach ($reviews as $review) {
        $totalRatings += $review->rating;
        $ratingCounts[$review->rating] = isset($ratingCounts[$review->rating])
            ? $ratingCounts[$review->rating] + 1
            : 1;
    }

    $averageRating = $totalRatings / $reviewCount;

    // Execute the ShowSummaryAction
    $response = $this->getJson("api/reviews/ratings/{$product_id}");
    $response
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($product_id, $averageRating, $reviewCount) {
            $json
                ->where('product_id', $product_id)
                ->where('raviewcount', $reviewCount)
                ->where('average_rating', $averageRating)
                ->etc();
        });
});

