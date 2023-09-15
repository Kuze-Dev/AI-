<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PublicCart;

use App\HttpTenantApi\Resources\CartLineResource;
use Domain\Cart\Actions\CheckoutAction;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CheckoutRequest;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('guest/carts/checkouts', apiResource: true, only: ['index', 'store'], names: 'guest.carts.checkouts'),
]
class GuestCheckoutController
{
    public function __construct(
        private readonly CheckoutAction $checkoutAction,
    ) {
    }

    public function index(Request $request): mixed
    {
        $sessionId = $request->bearerToken();

        if (is_null($sessionId)) {
            abort(403);
        }

        $validated = $request->validate([
            'reference' => 'required|string',
        ]);

        $reference = $validated['reference'];

        $cartLineQuery = CartLine::with(['purchasable' => function (MorphTo $query) {
            $query->morphWith([
                Product::class => ['media'],
                ProductVariant::class => ['product.media'],
            ]);
        }, 'media'])
            ->whereHas('cart', function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            })
            ->whereCheckoutReference($reference);

        $model = QueryBuilder::for($cartLineQuery)->jsonPaginate();

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
        $sessionId = $request->bearerToken();

        if (is_null($sessionId)) {
            abort(403);
        }

        $validatedData = $request->validated();

        $reference = $this->checkoutAction
            ->execute(CheckoutData::fromArray($validatedData));

        return response()
            ->json([
                'message' => 'Success',
                'reference' => $reference,
            ]);
    }
}
