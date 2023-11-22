<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Review;

use App\HttpTenantApi\Resources\ReviewResource;
use Domain\Review\Actions\ShowSummaryAction;
use Domain\Review\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpFoundation\JsonResponse;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    Resource('reviews', apiResource: true, only: ['show']),
]
class ReviewShowController
{
    public function show(string $review): JsonApiResourceCollection
    {
        return ReviewResource::collection(
            QueryBuilder::for(Review::whereProductId($review)->with('review_likes'))
                ->allowedIncludes([
                    'product',
                    'customer.media',
                    'media',
                ])->allowedFilters([
                    AllowedFilter::callback(
                        'rating',
                        function (Builder $query, string $value) {
                            match ($value) {
                                'asc' => $query->orderBy('rating', 'asc'),
                                'desc' => $query->orderBy('rating', 'desc'),
                                default => $query->where('rating', $value),
                            };
                        }
                    ),
                    AllowedFilter::callback(
                        'orderBy',
                        function (Builder $query, string $value) {
                            match ($value) {
                                'recent' => $query->orderBy('created_at', 'desc'),
                                'relevance' => $query->withCount('review_likes')->orderBy('review_likes_count', 'desc'),
                                'low' => $query->orderBy('rating', 'asc'),
                                'high' => $query->orderBy('rating', 'desc'),
                                default => '',
                            };
                        }
                    ),
                ])
                ->jsonPaginate()
        );
    }

    #[Get('reviews/ratings/{rating}')]
    public function showSummary(int $product_id, ShowSummaryAction $showSummaryAction): JsonResponse
    {
        $review = $showSummaryAction->execute($product_id);

        return response()->json($review, 200);
    }
}
