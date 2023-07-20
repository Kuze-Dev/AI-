<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\CartResource;
use Domain\Cart\Actions\DestroyCartAction;
use Domain\Cart\Models\Cart;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('carts', apiResource: true, only: ['index', 'destroy']),
    Middleware(['auth:sanctum'])
]
class CartController extends Controller
{
    public function index(): mixed
    {
        $model = QueryBuilder::for(
            Cart::with([
                'cartLines',
                'cartLines.purchasable' => function (MorphTo $query) {
                    $query->morphWith([
                        Product::class => ['media'],
                        ProductVariant::class => ['product.media'],
                    ]);
                },
                'cartLines.media',
            ])
                ->whereBelongsTo(auth()->user())
        )->allowedIncludes(['cartLines', 'cartLines.purchasable'])
            ->first();

        // return $model;

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
        $this->authorize('delete', $cart);

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
