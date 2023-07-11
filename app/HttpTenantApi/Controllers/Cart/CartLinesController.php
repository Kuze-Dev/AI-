<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use Domain\Cart\Actions\CartLineDestroyAction;
use Domain\Cart\Actions\CartQuantityUpdateAction;
use Domain\Cart\Actions\CreateCartAction;
use Domain\Cart\DataTransferObjects\CartQuantityUpdateData;
use Domain\Cart\DataTransferObjects\CreateCartData;
use Domain\Cart\Enums\CartActionResult;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartQuantityUpdateRequest;
use Domain\Cart\Requests\CreateCartLineRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;

#[
    Resource('carts/cartlines', apiResource: true, except: ['show', 'index']),
    Middleware(['auth:sanctum'])
]
class CartLinesController
{
    public function store(CreateCartLineRequest $request): mixed
    {
        $validatedData = $request->validated();

        $customer = auth()->user();

        $result = app(CreateCartAction::class)
            ->execute($customer, CreateCartData::fromArray($validatedData));

        if (CartActionResult::SUCCESS != $result) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => 'Invalid action',
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Successfully Added to Cart',
            ]);
    }

    public function update(CartQuantityUpdateRequest $request, CartLine $cartline): mixed
    {
        try {
            $validatedData = $request->validated();

            $validatedData['cartLineId'] = $cartline->id;

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

    public function destroy(CartLine $cartline): mixed
    {
        try {
            $result = app(CartLineDestroyAction::class)
                ->execute($cartline);

            if (!$result) {
                return response()->json([
                    'error' => 'Bad Request',
                    'message' => 'Invalid action',
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
