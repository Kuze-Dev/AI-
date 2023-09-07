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
        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        $model = QueryBuilder::for(
            Cart::with([
                'cartLines.purchasable' => function (MorphTo $query) {
                    $query->morphWith([
                        Product::class => ['media'],
                        ProductVariant::class => ['product.media'],
                    ]);
                },
                'cartLines.media',
            ])
                ->whereBelongsTo($customer)
        )->allowedIncludes(['cartLines.media'])
            ->first();

        if ($model && isset($model->cartLines)) {
            $model->cartLines = $model->cartLines->filter(function ($cartLine) {
                return $cartLine->purchasable !== null;
            });
        }

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

        if ( ! $result) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()
            ->noContent();
    }
}
