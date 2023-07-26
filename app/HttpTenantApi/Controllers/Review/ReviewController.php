<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Review;

use App\HttpTenantApi\Resources\ReviewResource;
use Domain\Order\Models\OrderLine;
use Domain\Product\Models\Product;
use Domain\Review\Models\Review;
use Domain\Review\Requests\ReviewStoreRequest;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpFoundation\JsonResponse;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Get;
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
        $review->rating = $validatedData['rating'];
        $review->comment = $validatedData['comment'];
        $review->order_line_id = $validatedData['order_line_id'];

        $orderLine = OrderLine::find($validatedData['order_line_id']);

        $review->order_id = $orderLine->order_id;

        if(isset($orderLine->purchasable_data['product']))
        {
            $review->product_id = $orderLine->purchasable_data['product']['id'];
        }else{
            $review->product_id = $orderLine->purchasable_data['id'];
        }
        
        $customer = auth()->user();
        if (!$validatedData['anonymous']) {
            $review->customer_id = $customer->id;
            $review->customer_name = $customer->first_name . ' ' . $customer->last_name;
            $review->customer_email = $customer->email;
        }

        if (isset($validatedData['media'])) {
            foreach ($validatedData['media'] as $imageUrl) {
                try {
                    $review->addMediaFromUrl($imageUrl)
                        ->toMediaCollection('review_product_media');
                } catch (Exception $e) {
                    // dd($e);
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

    #[Get('orderline-review/{orderLineId}')]
    public function showCustomerReview(string $orderLineId)
    {
        $customer = auth()->user();

        $review = QueryBuilder::for(Review::whereOrderLineId($orderLineId))
            ->allowedIncludes([
                'media',
            ])->first();

        return ReviewResource::make($review);
    }
}