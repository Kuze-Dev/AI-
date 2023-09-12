<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PublicCart;

use Domain\Cart\Actions\BulkDestroyCartLineAction;
use Domain\Cart\Requests\PublicCart\GuestBulkRemoveRequest;
use Spatie\RouteAttributes\Attributes\Post;

class GuestCartLinesRemoveController
{
    public function __construct(
        private readonly BulkDestroyCartLineAction $bulkDestroyCartLine,
    ) {
    }

    #[Post('/guest/carts/cartlines/bulk-remove', name: 'carts.bulk-remove')]
    public function __invoke(GuestBulkRemoveRequest $request): mixed
    {
        $validated = $request->validated();

        $cartLineIds = $validated['cart_line_ids'];

        $result = $this->bulkDestroyCartLine
            ->execute($cartLineIds);

        if (!$result) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()->noContent();
    }
}
