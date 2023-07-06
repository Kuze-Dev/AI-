<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\CartLineResource;
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

#[
    Prefix('carts'),
]
class CartController extends Controller
{
    #[Get('/{cartId}', name: 'cart.show.{cartId}')]
    public function show(int $cartId)
    {
        try {
            Cart::findOrFail($cartId);

            return CartLineResource::collection(
                QueryBuilder::for(
                    CartLine::with(["purchasable", 'variant'])
                        ->where("cart_id", $cartId)
                        ->whereNull("checked_out_at")
                )->allowedIncludes(['purchasable', 'variant'])
                    ->jsonPaginate()
            );
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart not found',
                ], 404);
        }
    }

    #[Post('/items', name: 'cart.items')]
    public function store(CartStoreRequest $request)
    {
        $validatedData = $request->validated();

        $result = app(CartStoreAction::class)
            ->execute(CartStoreData::fromArray($validatedData));

        if (CartActionResult::SUCCESS != $result) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => $result
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Successfully Added to Cart',
            ]);
    }

    #[Delete('/items/{cartLineId}', name: 'cart.items.{cartLineId}')]
    public function delete(int $cartLineId)
    {
        try {
            $cartLine = CartLine::findOrFail($cartLineId);

            $cartLine->delete();

            return response()
                ->json([
                    'message' => 'Cart item Deleted Successfully',
                ]);
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart item not found',
                ], 404);
        }
    }

    #[Delete('/clear/{cartId}', name: 'cart.clear.{cartId}')]
    public function clear(int $cartId)
    {
        try {
            $cart = Cart::findOrFail($cartId);

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
    public function bulkRemove(Request $request)
    {
        $cartLineIds = $request->input('cart_line_ids');

        try {
            $cartLines = CartLine::whereIn('id', $cartLineIds)
                // ->whereHas('cart', function ($query) use ($customer) {
                //     $query->where('customer_id', $customer->id);
                // })
                ->get();

            if (count($cartLineIds) !== $cartLines->count()) {
                throw new ModelNotFoundException;
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
    public function update(CartQuantityUpdateRequest $request)
    {
        $validatedData = $request->validated();

        $payload = CartQuantityUpdateData::fromArray($validatedData);

        $result = app(CartQuantityUpdateAction::class)
            ->execute($payload);

        if ($result instanceof CartLine) {
            return response()
                ->json([
                    'message' => 'Cart quantity updated successfully'
                    // 'data' => $result,
                ]);
        }

        return response()->json([
            'error' => 'Bad Request',
            'message' => $result
        ], 400);
    }

    #[Post('/items/notes', name: 'cart.items.notes')]
    public function updateNotes(CartNotesUpdateRequest $request)
    {
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

        return response()->json([
            'error' => 'Bad Request',
            'message' => $result
        ], 400);
    }

    #[Delete('/items/notes/{cartLineId}', name: 'cart.items.notes.{cartLineId}')]
    public function deleteNotesImage(int $cartLineId)
    {
        try {
            $cartLine = CartLine::findOrFail($cartLineId);

            $cartLine->clearMediaCollection('cart_line_notes');

            return response()
                ->json([
                    'message' => 'Image deleted successfully',
                ]);
        } catch (ModelNotFoundException $e) {
            return response()
                ->json([
                    'error' => 'Cart item not found',
                ], 404);
        }
    }
}
