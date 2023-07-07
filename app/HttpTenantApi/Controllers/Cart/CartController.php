<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\CartResource;
use Domain\Cart\Actions\CartNotesUpdateAction;
use Domain\Cart\Actions\CartQuantityUpdateAction;
use Domain\Cart\Actions\CartStoreAction;
use Domain\Cart\DataTransferObjects\CartNotesUpdateData;
use Domain\Cart\DataTransferObjects\CartQuantityUpdateData;
use Domain\Cart\DataTransferObjects\CartStoreData;
use Domain\Cart\Enums\CartActionResult;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartNotesUpdateRequest;
use Domain\Cart\Requests\CartQuantityUpdateRequest;
use Domain\Cart\Requests\CartStoreRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Middleware;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use TiMacDonald\JsonApi\JsonApiResource;

#[
    Prefix('carts'),
    Middleware(['auth:sanctum'])
]
class CartController extends Controller
{
    #[Get('/{cartId}', name: 'cart.show.{cartId}')]
    public function show(int $cartId): JsonApiResource|JsonResponse
    {
        try {
            $customerId = auth()->user() ? auth()->user()->id : null;

            if (!$customerId) {
                return response()
                    ->json([
                        'error' => 'Access denied',
                    ], 403);
            }

            Cart::where('id', $cartId)->whereCustomerId($customerId)->firstOrFail();

            $model = QueryBuilder::for(
                Cart::with(['cartLines', 'cartLines.purchasable', 'cartLines.media'])
                    ->whereHas('cartLines', function (Builder $query) {
                        $query->whereNull('checked_out_at');
                    })
                    ->where('id', $cartId)
                    ->whereCustomerId($customerId)
            )->allowedIncludes(['cartLines', 'cartLines.purchasable'])
                ->firstOrFail();

            return CartResource::make($model);
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart not found',
                ], 404);
        }
    }

    #[Post('/items', name: 'cart.items')]
    public function store(CartStoreRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $validatedData['customer_id'] = auth()->user()->id;

        $result = app(CartStoreAction::class)
            ->execute(CartStoreData::fromArray($validatedData));

        if (CartActionResult::SUCCESS != $result) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => $result,
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Successfully Added to Cart',
            ]);
    }

    #[Delete('/items/{cartLineId}', name: 'cart.items.{cartLineId}')]
    public function delete(int $cartLineId): JsonResponse
    {
        try {
            $customerId = auth()->user()->id;

            $cartLine = CartLine::whereHas(
                'cart',
                function (Builder $query) use ($customerId) {
                    $query->whereCustomerId($customerId);
                }
            )->where('id', $cartLineId)
                ->whereNull('checked_out_at')->firstOrFail();

            $cartLine->delete();

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

    #[Delete('/clear/{cartId}', name: 'cart.clear.{cartId}')]
    public function clear(int $cartId): JsonResponse
    {
        try {
            $customerId = auth()->user()->id;

            $cart = Cart::where('id', $cartId)->whereCustomerId($customerId)->firstOrFail();

            $cart->delete();

            return response()
                ->json([
                    'message' => 'Cart Cleared Successfully',
                ]);
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart not found',
                ], 404);
        }
    }

    #[Post('/bulk-remove', name: 'cart.bulk-remove')]
    public function bulkRemove(Request $request): JsonResponse
    {
        try {
            $cartLineIds = $request->input('cart_line_ids');

            $customerId = auth()->user()->id;

            $cartLines = CartLine::whereIn('id', $cartLineIds)
                ->whereHas('cart', function ($query) use ($customerId) {
                    $query->whereCustomerId($customerId);
                })
                ->whereNull('checked_out_at')
                ->get();

            if (count($cartLineIds) !== $cartLines->count()) {
                throw new ModelNotFoundException();
            }

            $cartLines = CartLine::whereIn('id', $cartLineIds)->get();

            $cartLines->each(function ($cartLine) {
                $cartLine->delete();
            });

            return response()
                ->json([
                    'message' => 'Cart lines Deleted Successfully',
                ]);
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart lines not found',
                ], 404);
        }
    }

    #[Patch('/items/quantity/{cartLineId}', name: 'cart.items.quantity.{cartLineId}')]
    public function update(CartQuantityUpdateRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $payload = CartQuantityUpdateData::fromArray($validatedData);

            $result = app(CartQuantityUpdateAction::class)
                ->execute($payload);

            if ($result instanceof CartLine) {
                return response()
                    ->json([
                        'message' => 'Cart quantity updated successfully',
                        // 'data' => $result,
                    ]);
            }
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart line not found',
                ], 404);
        }
    }

    #[Post('/items/notes', name: 'cart.items.notes')]
    public function updateNotes(CartNotesUpdateRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $payload = CartNotesUpdateData::fromArray($validatedData);

            $result = app(CartNotesUpdateAction::class)
                ->execute($payload);

            if ($result instanceof CartLine) {
                return response()
                    ->json([
                        'message' => 'Notes updated successfully',
                        // 'data' => $result,
                    ]);
            }
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart line not found',
                ], 404);
        }
    }
}
