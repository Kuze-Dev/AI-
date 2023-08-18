<?php

declare(strict_types=1);

namespace Domain\Review\Actions;

use Domain\Customer\Models\Customer;
use Domain\Review\Models\Review;
use Domain\Review\Models\ReviewLike;

class EditLikeAction
{
    public function execute(int $reviewId, Customer $customer): Review
    {
        $review = Review::findOrFail($reviewId);
        $hasLiked = $review->review_likes->contains('customer_id', $customer->id);

        if ($hasLiked) {
            $like = ReviewLike::where('customer_id', $customer->id)->where('review_id', $review->id)->first();
            if ($like) {
                $like->delete();
            }
        } else {
            $review->review_likes()->create(['customer_id' => $customer->id]);
        }

        $review->load('review_likes');

        return $review;
    }
}
