<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use Domain\Cart\Actions\CartDestroyAction;
use Domain\Cart\Actions\CartLineBulkDestroyAction;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[
    Prefix('carts'),
    Middleware(['auth:sanctum'])
]
class CartRemoveController
{
    #[Delete('clear/{cartId}', name: 'carts.clear.{cartId}')]
    public function clear(int $cartId): mixed
    {
        try {
            $cart = Cart::findOrFail($cartId);

            $result = app(CartDestroyAction::class)
                ->execute($cart);

            if ( ! $result) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Invalid action',
                ], 400);
            }

            return response()
                ->json([
                    'message' => 'Cart Cleared Successfully',
                ]);
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart not found',
                ], 404);
        }
    }

    #[Post('/bulk-remove', name: 'carts.bulk-remove')]
    public function bulk(Request $request): mixed
    {
        try {
            $validated = $request->validate([
                'cart_line_ids' => 'required|array',
                'cart_line_ids.*' => [
                    'required',
                    Rule::exists(CartLine::class, 'id'),
                ],
            ]);

            $cartLineIds = $validated['cart_line_ids'];

            $result = app(CartLineBulkDestroyAction::class)
                ->execute($cartLineIds);

            if ( ! $result) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Invalid action',
                ], 400);
            }

            return response([
                'message' => 'Cart lines Deleted Successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart lines not found',
                ], 404);
        }
    }
}
