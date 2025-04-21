<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PrivateCart;

use App\HttpTenantApi\Resources\CartLineResource;
use Domain\Cart\Actions\CheckoutAction;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Helpers\PrivateCart\CartLineQuery;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CheckoutRequest;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('carts/checkouts', apiResource: true, only: ['index', 'store'], names: 'carts.checkouts'),
    Middleware(['auth:sanctum'])
]
class CheckoutController
{
    public function index(Request $request): mixed
    {
        $validated = $request->validate([
            'reference' => 'required|string',
        ]);

        $reference = $validated['reference'];

        $cartLines = CartLine::with(['purchasable' => function (MorphTo $query) {
            $query->morphWith([
                Product::class => ['media'],
                ProductVariant::class => ['product.media'],
            ]);
        }, 'media'])
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(customer_logged_in());
            })
            ->whereCheckoutReference($reference);

        $cartLineIds = $cartLines->pluck('uuid')->toArray();

        $model = app(CartLineQuery::class)->execute($cartLineIds);

        if ($model->isNotEmpty()) {
            $expiredCartLines = $model->where('checkout_expiration', '<=', now());

            // Check if there are expired cart lines
            if ($expiredCartLines->isNotEmpty()) {
                return response()->json([
                    'message' => 'Key has been expired, checkout again to revalidate your cart',
                ], 400);
            }

            return CartLineResource::collection($model);
        }

        return response()->json([
            'data' => [],
        ], 200);
    }

    public function store(CheckoutRequest $request): mixed
    {
        $validatedData = $request->validated();

        $reference = app(CheckoutAction::class)
            ->execute(CheckoutData::fromArray($validatedData));

        return response()
            ->json([
                'message' => 'Success',
                'reference' => $reference,
            ]);
    }
}
