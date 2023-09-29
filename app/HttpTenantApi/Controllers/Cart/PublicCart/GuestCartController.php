<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PublicCart;

use App\Features\ECommerce\AllowGuestOrder;
use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\CartResource;
use Domain\Cart\Actions\DestroyCartAction;
use Domain\Cart\Actions\SanitizeCartAction;
use Domain\Cart\Helpers\PublicCart\AuthorizeGuestCart;
use Domain\Cart\Models\Cart;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('guest/carts', apiResource: true, only: ['index', 'destroy'], names: 'guest.carts'),
    Middleware(['feature.tenant:' . AllowGuestOrder::class])
]
class GuestCartController extends Controller
{
    public function index(Request $request): mixed
    {
        $sessionId = $request->bearerToken();

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
                ->where('session_id', $sessionId)
        )->allowedIncludes(['cartLines.media'])
            ->first();

        if ($model && isset($model->cartLines)) {
            $model = app(SanitizeCartAction::class)->sanitizeGuest($model);
        }

        if ($model) {
            return CartResource::make($model);
        }

        return response()
            ->json([
                'data' => [],
            ], 200);
    }

    public function destroy(Request $request, Cart $cart): mixed
    {
        $sessionId = $request->bearerToken();

        $allowed = app(AuthorizeGuestCart::class)->execute($cart, $sessionId);

        if ( ! $allowed) {
            abort(403);
        }

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
