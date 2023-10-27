<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PublicCart;

use App\Features\ECommerce\AllowGuestOrder;
use Domain\Cart\Actions\BulkDestroyCartLineAction;
use Domain\Cart\Requests\PublicCart\GuestBulkRemoveRequest;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;

#[
    Middleware(['feature.tenant:'.AllowGuestOrder::class])
]
class GuestCartLinesRemoveController
{
    #[Post('/guest/carts/cartlines/bulk-remove', name: 'guest.carts.cartlines.bulk-remove')]
    public function __invoke(GuestBulkRemoveRequest $request): mixed
    {
        $sessionId = $request->bearerToken();

        if (is_null($sessionId)) {
            abort(403);
        }

        $validated = $request->validated();

        $cartLineIds = $validated['cart_line_ids'];

        $result = app(BulkDestroyCartLineAction::class)
            ->execute($cartLineIds);

        if (! $result) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()->noContent();
    }
}
