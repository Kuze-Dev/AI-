<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Review;

use App\Attributes\CurrentApiCustomer;
use Domain\Customer\Models\Customer;
use Domain\Review\Actions\EditLikeAction;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Prefix;
use Symfony\Component\HttpFoundation\JsonResponse;

#[
    Prefix('reviews'),
    Middleware(['auth:sanctum'])
]
class LikeController
{
    #[Patch('like/{reviewId}')]
    public function __invoke(int $reviewId, EditLikeAction $editLikeAction,#[CurrentApiCustomer] Customer $customer): JsonResponse
    {

        $editLikeAction->execute($reviewId, $customer);

        return response()->json(['message' => 'Like has been updated successfully.'], 200);
    }
}
