<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\HttpTenantApi\Resources\CartResource;
use Domain\Cart\Actions\DestroyCartAction;
use Domain\Cart\Models\Cart;
use Domain\Product\Models\ProductVariant;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('carts', apiResource: true, only: ['index', 'destroy']),
    Middleware(['auth:sanctum'])
]
class CartController
{
    public function index(): mixed
    {
        $model = QueryBuilder::for(
            Cart::with([
                'cartLines',
                'cartLines.purchasable' => function (MorphTo $query) {
                    $query->morphWith([
                        ProductVariant::class => ['product.media'],
                    ]);
                },
                'cartLines.media',
            ])
                ->whereHas('cartLines', function (Builder $query) {
                    $query->whereNull('checked_out_at');
                })
                ->whereBelongsTo(auth()->user())
        )->allowedIncludes(['cartLines', 'cartLines.purchasable'])
            ->first();

        if ($model) {
            return CartResource::make($model);
        }

        return response()
            ->json([
                'data' => [],
            ], 200);
    }

    public function destroy(Cart $cart): mixed
    {
        $result = app(DestroyCartAction::class)
            ->execute($cart);

        if (!$result) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()
            ->noContent();
    }
}
