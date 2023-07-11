<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\HttpTenantApi\Resources\CartLineResource;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CheckoutRequest;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\RouteAttributes\Attributes\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('carts/checkouts', apiResource: true, only: ['index', 'store']),
    Middleware(['auth:sanctum'])
]
class CheckoutController
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
        ]);

        $reference = $validated['reference'];

        if ( ! $reference) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => 'Invalid reference.',
            ], 400);
        }

        $cartLineQuery = CartLine::with(['purchasable' => function (MorphTo $query) {
            $query->morphWith([
                ProductVariant::class => ['product.media'],
            ]);
        }, 'media'])
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereCheckoutReference($reference)
            ->where('checkout_expiration', '>', now());

        return CartLineResource::collection(
            QueryBuilder::for(
                $cartLineQuery
            )->jsonPaginate()
        );
    }

    public function store(CheckoutRequest $request): mixed
    {
        $validatedData = $request->validated();

        $payload = CheckoutData::fromArray($validatedData);

        $cartLinesForCheckout = CartLine::with('purchasable')
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->whereIn('id', $payload->cart_line_ids)
            ->where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('purchasable_type', Product::class)
                        ->whereExists(function ($subSubQuery) {
                            $subSubQuery->select('id')
                                ->from('products')
                                ->whereColumn('products.id', 'cart_lines.purchasable_id')
                                ->where('stock', '>', DB::raw('cart_lines.quantity'));
                        });
                })->orWhere(function ($subQuery) {
                    $subQuery->where('purchasable_type', ProductVariant::class)
                        ->whereExists(function ($subSubQuery) {
                            $subSubQuery->select('id')
                                ->from('product_variants')
                                ->whereColumn('product_variants.id', 'cart_lines.purchasable_id')
                                ->where('stock', '>', DB::raw('cart_lines.quantity'));
                        });
                });
            })
            ->get();

        if ($cartLinesForCheckout->count() !== count($payload->cart_line_ids)) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $cartLineIds = $cartLinesForCheckout->pluck('id');

        $checkoutReference = Str::upper(Str::random(12));

        CartLine::whereIn('id', $cartLineIds)
            ->update([
                'checkout_reference' => $checkoutReference,
                'checkout_expiration' => now()->addMinutes(20),
            ]);

        return response()
            ->json([
                'message' => 'Success',
                'reference' => $checkoutReference,
            ]);
    }
}
