<?php

declare(strict_types=1);

namespace Domain\Review\Actions;

use Domain\Review\Models\Review;

class DestroyReviewAction
{
    public function execute(int $review): Review
    {
        $review = Review::where('id', $review)
            ->firstOrFail();

        $review->delete();

        return $review;
    }
}
