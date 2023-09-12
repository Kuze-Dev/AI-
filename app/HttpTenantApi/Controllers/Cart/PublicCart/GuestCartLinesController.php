<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PublicCart;

use Domain\Cart\Actions\UpdateCartLineAction;
use Domain\Cart\DataTransferObjects\UpdateCartLineData;
use Domain\Cart\DataTransferObjects\CreateCartData;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\UpdateCartLineRequest;
use Domain\Cart\Requests\CreateCartLineRequest;
use Spatie\RouteAttributes\Attributes\Resource;
use App\Http\Controllers\Controller;
use Domain\Cart\Actions\CreateCartLineAction;
use Domain\Cart\Actions\DestroyCartLineAction;
use Domain\Cart\Actions\PublicCart\GuestCreateCartAction;
use Domain\Cart\Helpers\PublicCart\AuthorizeGuestCart;
use Domain\Cart\Models\Cart;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

#[
    Resource('guest/carts/cartlines', apiResource: true, except: ['show', 'index']),
]
class GuestCartLinesController extends Controller
{
    public function __construct(
        private readonly AuthorizeGuestCart $authorize,
        private readonly GuestCreateCartAction $createCart,
        private readonly CreateCartLineAction $createCartLine,
        private readonly UpdateCartLineAction $updateCartLine,
        private readonly DestroyCartLineAction $destroyCartLine
    ) {
    }

    public function store(CreateCartLineRequest $request): mixed
    {
        $validatedData = $request->validated();
        $sessionId = $request->bearerToken() ?? null;

        $cart = $this->createCart->execute($sessionId);

        if ( ! $cart instanceof Cart) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        $cartline = $this->createCartLine->execute($cart, CreateCartData::fromArray($validatedData));

        if ( ! $cartline instanceof CartLine) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Successfully Added to Cart',
                'session_id' => $cart->session_id,
            ]);
    }

    public function update(UpdateCartLineRequest $request, CartLine $cartline): mixed
    {
        $cartline->load('cart');
        $sessionId = $request->bearerToken();

        $allowed = $this->authorize->execute($cartline, $sessionId);

        if ( ! $allowed) {
            abort(403);
        }

        $validatedData = $request->validated();

        try {
            $result = $this->updateCartLine
                ->execute($cartline, UpdateCartLineData::fromArray($validatedData));

            if ($result instanceof CartLine) {
                return response()
                    ->json([
                        'message' => 'Cart updated successfully',
                    ]);
            }
        } catch (Throwable $th) {
            if ($th instanceof BadRequestException) {
                return response()->json([
                    'message' => $th->getMessage(),
                ], 400);
            }
        }

        return response()->json([
            'message' => 'Something went wrong',
        ], 400);
    }

    public function destroy(Request $request, CartLine $cartline): mixed
    {
        $cartline->load('cart');
        $sessionId = $request->bearerToken();

        $allowed = $this->authorize->execute($cartline, $sessionId);

        if ( ! $allowed) {
            abort(403);
        }

        $result = $this->destroyCartLine->execute($cartline);

        if ( ! $result) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()->noContent();
    }
}
