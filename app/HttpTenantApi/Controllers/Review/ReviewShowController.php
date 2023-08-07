<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Review;

use Domain\Review\Models\Review;
use Illuminate\Support\Facades\DB;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpFoundation\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;
use TiMacDonald\JsonApi\JsonApiResourceCollection;
use App\HttpTenantApi\Resources\ReviewResource;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;

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
                    'customer.media',
                    'media',
                ]) ->allowedFilters([
                    AllowedFilter::callback(
                        'rating',
                        function (Builder $query, string $value) {
                            match ($value) {
                                'asc' => $query->orderBy('rating', 'asc'),
                                'desc' => $query->orderBy('rating', 'desc'),
                                default => '',
                            };
                        }
                    ),
                ])
                ->jsonPaginate()
        );
    }

    #[Get('reviews/ratings/{rating}')]
    public function showSummary(string $product_id): JsonResponse
    {
        $review = Review::where('product_id', $product_id);

        $reviewCount = $review->count();
        $averageRating = $review->avg('rating');

        $ratingCounts = $review
            ->select('rating', DB::raw('COUNT(rating) as rating_count'))
            ->groupBy('rating')->get()->toArray();

        return response()->json([
            'product_id' => $product_id,
            'raviewcount' => $reviewCount,
            'average_rating' => $averageRating,
            'rating_counts' => $ratingCounts,
        ]);
    }
}
