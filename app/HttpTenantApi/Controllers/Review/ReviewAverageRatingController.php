<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Review;

use Domain\Review\Models\Review;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpFoundation\JsonResponse;

#[
    Resource('average-rating', apiResource: true, only: ['show']),
]
class ReviewAverageRatingController
{
    public function show(string $average_rating): JsonResponse
    {
        $averageRating = Review::where('product_id', $average_rating)->avg('rating');

        return response()->json(['product_id' => $average_rating, 'average_ratings' => $averageRating]);

    }
}
