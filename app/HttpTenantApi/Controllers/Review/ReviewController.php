<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Review;

use App\HttpTenantApi\Resources\ReviewResource;
use Domain\Review\Models\Review;
use Domain\Review\Requests\ReviewStoreRequest;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpFoundation\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;
use TiMacDonald\JsonApi\JsonApiResourceCollection;
use Spatie\RouteAttributes\Attributes\Middleware;
use Exception;

#[
    Resource('reviews', apiResource: true, except: ['index', 'update']),
    Middleware(['auth:sanctum', 'feature.tenant:' . ECommerceBase::class])
]
class ReviewController
{
    public function show(string $review): JsonApiResourceCollection
    {
        return ReviewResource::collection(
            QueryBuilder::for(Review::whereProductId($review))
                ->allowedIncludes([
                    'product',
                    'customer',
                ])
                ->get()
        );
    }

    public function store(ReviewStoreRequest $request, Review $review): JsonResponse
    {

        $validatedData = $request->validated();
        $review->title = $validatedData['title'];
        $review->rating = $validatedData['rating'];
        $review->comment = $validatedData['comment'];
        $review->product_id = $validatedData['product_id'];
        $review->order_id = $validatedData['order_id'];

        $customer = auth()->user();
        if(!$validatedData['anonymous']){
            $review->customer_id = $customer->id;
        }
 
        if ($validatedData['product_review_images'] !== null) {
            foreach ($validatedData['product_review_images'] as $imageUrl) {
                try {
                    $review->addMediaFromUrl($imageUrl)
                        ->toMediaCollection('product_review_images');
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
