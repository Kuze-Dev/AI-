<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use Domain\Cart\Actions\CartLineDestroyAction;
use Domain\Cart\Actions\CartQuantityUpdateAction;
use Domain\Cart\Actions\CartStoreAction;
use Domain\Cart\DataTransferObjects\CartQuantityUpdateData;
use Domain\Cart\DataTransferObjects\CartStoreData;
use Domain\Cart\Enums\CartActionResult;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartQuantityUpdateRequest;
use Domain\Cart\Requests\CartStoreRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('carts/items', apiResource: true, except: ['show', 'index']),
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
                'message' => 'Invalid action'
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Successfully Added to Cart',
            ]);
    }

    public function update(CartQuantityUpdateRequest $request, int $cartLineId): mixed
    {
        try {
            CartLine::findOrFail($cartLineId);

            $validatedData = $request->validated();

            $validatedData['cartLineId'] = $cartLineId;

            $payload = CartQuantityUpdateData::fromArray($validatedData);

            $result = app(CartQuantityUpdateAction::class)
                ->execute($payload);

            if ($result instanceof CartLine) {
                return response()
                    ->json([
                        'message' => 'Cart quantity updated successfully',
                    ]);
            }
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart line not found',
                ], 404);
        }

        return response()
            ->json([
                'message' => 'Cart didnt update',
            ]);
    }

    public function destroy(int $cartLineId): mixed
    {
        try {
            $cartLine = CartLine::findOrFail($cartLineId);

            $result = app(CartLineDestroyAction::class)
                ->execute($cartLine);

            if (!$result) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Invalid action'
                ], 400);
            }

            return response()
                ->json([
                    'message' => 'Cart item Deleted Successfully',
                ]);
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart line not found',
                ], 404);
        }
    }
}
