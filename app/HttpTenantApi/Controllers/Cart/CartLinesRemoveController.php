<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use Domain\Cart\Actions\BulkDestroyCartLineAction;
use Domain\Cart\Models\CartLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[
    Middleware(['auth:sanctum'])
]
class CartLinesRemoveController
{
    #[Post('carts/cartlines/bulk-remove', name: 'carts.bulk-remove')]
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'cart_line_ids' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $cartLineIds = $value;

                    $cartLines = CartLine::query()
                        ->whereIn('id', $cartLineIds)
                        ->whereNull('checked_out_at');

                    if (count($cartLineIds) !== $cartLines->count()) {
                        $fail('Cart lines not found');
                    }
                },
            ],
            'cart_line_ids.*' => [
                'required',
                Rule::exists(CartLine::class, 'id'),
            ],
        ]);

        $cartLineIds = $validated['cart_line_ids'];

        $result = app(BulkDestroyCartLineAction::class)
            ->execute($cartLineIds);

        if (!$result) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()->noContent();
    }
}
