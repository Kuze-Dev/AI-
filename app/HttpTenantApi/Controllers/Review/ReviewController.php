<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Review;

use Illuminate\Container\Attributes\CurrentUser;
use App\HttpTenantApi\Resources\ReviewResource;
use Domain\Customer\Models\Customer;
use Domain\Review\Actions\CreateReviewAction;
use Domain\Review\Actions\DestroyReviewAction;
use Domain\Review\DataTransferObjects\CreateReviewData;
use Domain\Review\Models\Review;
use Domain\Review\Requests\ReviewStoreRequest;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpFoundation\JsonResponse;

#[
    Resource('reviews', apiResource: true, only: ['destroy', 'store']),
    Middleware(['auth:sanctum'])
]
class ReviewController
{
    public function store(ReviewStoreRequest $request, CreateReviewAction $createReviewAction, #[CurrentUser('sanctum')] Customer $customer): JsonResponse
    {

        $validatedData = $request->validated();


        $createReviewAction->execute(CreateReviewData::fromArray($validatedData), $customer);

        return response()->json(['message' => 'Review item created successfully'], 201);
    }

    public function destroy(int $review, DestroyReviewAction $destroyReviewAction): JsonResponse
    {
        $destroyReviewAction->execute($review);

        return response()->json(['message' => 'Review item deleted'], 200);
    }

    #[Get('orderline-review/{orderLineId}')]
    public function showCustomerReview(string $orderLineId): ReviewResource
    {
        $review = QueryBuilder::for(Review::whereOrderLineId($orderLineId))
            ->allowedIncludes([
                'media',
            ])->first();

        return ReviewResource::make($review);
    }
}
