<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use Domain\Cart\Actions\CartStoreAction;
use Domain\Cart\DataTransferObjects\CartStoreData;
use Domain\Cart\Enums\CartActionResult;
use Domain\Cart\Requests\CartStoreRequest;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('carts/items', apiResource: true, except: 'show'),
    Middleware(['auth:sanctum'])
]
class CartItemsController
{
    public function store(CartStoreRequest $request): mixed
    {
        $validatedData = $request->validated();

        $validatedData['customer_id'] = auth()->user()?->id;

        $result = app(CartStoreAction::class)
            ->execute(CartStoreData::fromArray($validatedData));

        if (CartActionResult::SUCCESS != $result) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => $result
                // 'message' => 'Invalid action'
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Successfully Added to Cart',
            ]);
    }
}
