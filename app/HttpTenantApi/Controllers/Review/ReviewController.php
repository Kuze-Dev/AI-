<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Review;

use Domain\Review\Models\Review;
use Domain\Review\Requests\ReviewStoreRequest;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpFoundation\JsonResponse;
use Spatie\RouteAttributes\Attributes\Middleware;
use Exception;

#[
    Resource('reviews', apiResource: true, only: ['destroy', 'store']),
    Middleware(['auth:sanctum'])
]
class ReviewController
{
    public function store(ReviewStoreRequest $request, Review $review): JsonResponse
    {

        $validatedData = $request->validated();
        $review->title = $validatedData['title'];
        $review->rating = $validatedData['rating'];
        $review->comment = $validatedData['comment'];
        $review->product_id = $validatedData['product_id'];
        $review->order_id = $validatedData['order_id'];
        $review->order_line_id = $validatedData['order_line_id'];

        $customer = auth()->user();
        if( ! $validatedData['anonymous']) {
            $review->customer_id = $customer->id;
        }

        if ($validatedData['media'] !== null) {
            foreach ($validatedData['media'] as $imageUrl) {
                try {
                    $review->addMediaFromUrl($imageUrl)
                        ->toMediaCollection('media');
                } catch (Exception $e) {
                    dd($e);
                }
            }
        }

        $review->save();

        return response()->json(['message' => 'Review item created successfully'], 201);
    }

    public function destroy(int $review): JsonResponse
    {
        $review = Review::where('id', $review)
            ->firstOrFail();

        $review->delete();

        return response()->json(['message' => 'Review item deleted']);
    }
}
