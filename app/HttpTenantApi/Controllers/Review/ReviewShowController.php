<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Review;

use Domain\Review\Models\Review;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpFoundation\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;
use TiMacDonald\JsonApi\JsonApiResourceCollection;
use App\HttpTenantApi\Resources\ReviewResource;
#[
    Resource('reviews', apiResource: true, only: ['show']),
]
class ReviewShowController
{
    public function show(string $review): JsonApiResourceCollection
    {
        return ReviewResource::collection(
            QueryBuilder::for(Review::whereProductId($review))
                ->allowedIncludes([
                    'product',
                    'customer',
                    'order_line'
                ])
                ->get()
        );
    }


    #[Get('reviews/ratings/{rating}')]
    public function showRating(string $rating): JsonResponse
    {
        $averageRating = Review::where('product_id', $rating)->avg('rating');

        return response()->json(['product_id' => $rating, 'average_ratings' => $averageRating]);

    }   
}
