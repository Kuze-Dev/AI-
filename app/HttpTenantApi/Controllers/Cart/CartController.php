<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\HttpTenantApi\Resources\CartResource;
use Domain\Cart\Models\Cart;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Middleware;
use Illuminate\Database\Eloquent\Builder;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('carts', apiResource: true, except: 'show'),
    Middleware(['auth:sanctum'])
]
class CartController
{
    public function index(): mixed
    {
        $model = QueryBuilder::for(
            Cart::with(['cartLines', 'cartLines.purchasable', 'cartLines.media'])
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
}
