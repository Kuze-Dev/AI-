<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PrivateCart;

use Domain\Cart\Actions\BulkDestroyCartLineAction;
use Domain\Cart\Requests\BulkRemoveRequest;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[
    Middleware(['auth:sanctum'])
]
class CartLinesRemoveController
{
    #[Post('carts/cartlines/bulk-remove', name: 'carts.cartlines.bulk-remove')]
    public function __invoke(BulkRemoveRequest $request): mixed
    {
        $validated = $request->validated();

        $cartLineIds = $validated['cart_line_ids'];

        $result = app(BulkDestroyCartLineAction::class)
            ->execute($cartLineIds);

        if (! $result) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()->noContent();
    }
}
