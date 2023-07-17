<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\HttpTenantApi\Resources\CartLineResource;
use Domain\Cart\Actions\CheckoutAction;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Enums\CartActionResult;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CheckoutRequest;
use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\RouteAttributes\Attributes\Middleware;
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

        $cartLineQuery = CartLine::with(['purchasable' => function (MorphTo $query) {
            $query->morphWith([
                ProductVariant::class => ['product.media'],
            ]);
        }, 'media'])
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereCheckoutReference($reference);

        $model = QueryBuilder::for($cartLineQuery)->jsonPaginate();

        if ($model->isNotEmpty()) {
            $expiredCartLines = $model->where('checkout_expiration', '<=', now());

            // Check if there are expired cart lines
            if ($expiredCartLines->isNotEmpty()) {
                return response()->json([
                    'message' => 'Key has been expired, checkout again',
                ], 200);
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

        if (CartActionResult::FAILED == $reference) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Success',
                'reference' => $reference,
            ]);
    }
}
