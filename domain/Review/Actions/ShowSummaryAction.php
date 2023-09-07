<?php

declare(strict_types=1);

namespace Domain\Review\Actions;

use Domain\Review\Models\Review;
use Illuminate\Support\Facades\DB;

class ShowSummaryAction
{
    public function execute(int $product_id): array
    {
        $review = Review::where('product_id', $product_id);

        $reviewCount = $review->count();
        $averageRating = $review->avg('rating');

        $ratingCounts = $review
            ->select('rating', DB::raw('COUNT(rating) as rating_count'))
            ->groupBy('rating')->get()->toArray();

        return [
            'product_id' => $product_id,
            'raviewcount' => $reviewCount,
            'average_rating' => $averageRating,
            'rating_counts' => $ratingCounts,
        ];
    }
}
