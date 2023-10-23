<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PrivateCart;

use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\CartResource;
use Domain\Cart\Actions\DestroyCartAction;
use Domain\Cart\Models\Cart;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Domain\Tier\Models\Tier;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

        /** @var \Domain\Tier\Models\Tier $tier */
        $tier = $customer->tier ?? Tier::query()->where('name', config('domain.tier.default'))->first();

        $model = QueryBuilder::for(
            Cart::with([
                'cartLines.purchasable' => function (MorphTo $query) use ($tier) {
                    $query->morphWith([
                        Product::class => [
                            'media',
                            'productTier' => function (BelongsToMany $query) use ($tier) {
                                $query->where('tier_id', $tier->id);
                            },
                        ],
                        ProductVariant::class => [
                            'product.media',
                            'product.productTier' => function (BelongsToMany $query) use ($tier) {
                                $query->where('tier_id', $tier->id);
                            },
                        ],
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
