<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use Domain\Cart\Actions\DestroyCartLineAction;
use Domain\Cart\Actions\UpdateCartLineAction;
use Domain\Cart\Actions\CreateCartAction;
use Domain\Cart\DataTransferObjects\UpdateCartLineData;
use Domain\Cart\DataTransferObjects\CreateCartData;
use Domain\Cart\Enums\CartActionResult;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\UpdateCartLineRequest;
use Domain\Cart\Requests\CreateCartLineRequest;
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
                'message' => 'Invalid action',
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Successfully Added to Cart',
            ]);
    }

    public function update(UpdateCartLineRequest $request, CartLine $cartline): mixed
    {
        $validatedData = $request->validated();

        $result = app(UpdateCartLineAction::class)
            ->execute($cartline, UpdateCartLineData::fromArray($validatedData));

        if ($result instanceof CartLine) {
            return response()
                ->json([
                    'message' => 'Cart updated successfully',
                ]);
        }

        return response()
            ->json([
                'message' => 'Cart didnt update',
            ], 400);
    }

    public function destroy(CartLine $cartline): mixed
    {
        $result = app(DestroyCartLineAction::class)
            ->execute($cartline);

        if (!$result) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()->noContent();
    }
}
