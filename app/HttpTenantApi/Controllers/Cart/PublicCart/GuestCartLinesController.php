<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PublicCart;

use Domain\Cart\Actions\DestroyCartLineAction;
use Domain\Cart\Actions\UpdateCartLineAction;
use Domain\Cart\Actions\CreateCartAction;
use Domain\Cart\DataTransferObjects\UpdateCartLineData;
use Domain\Cart\DataTransferObjects\CreateCartData;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\UpdateCartLineRequest;
use Domain\Cart\Requests\CreateCartLineRequest;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;
use App\Http\Controllers\Controller;
use Domain\Cart\Actions\CreateCartLineAction;
use Domain\Cart\Actions\PublicCart\GuestCreateCartAction;
use Domain\Cart\Models\Cart;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

#[
    Resource('guest/carts/cartlines', apiResource: true, except: ['show', 'index']),
]
class GuestCartLinesController extends Controller
{
    public function store(CreateCartLineRequest $request): mixed
    {
        $validatedData = $request->validated();
        $sessionId = $request->bearerToken() ?? null;

        $cart = app(GuestCreateCartAction::class)->execute($sessionId);

        if (!$cart instanceof Cart) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        $cartline = app(CreateCartLineAction::class)
            ->execute($cart, CreateCartData::fromArray($validatedData));

        if (!$cartline instanceof CartLine) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()
            ->json([
                'message' => 'Successfully Added to Cart',
                'session_id' => $cart->session_id
            ]);
    }

    public function update(UpdateCartLineRequest $request, CartLine $cartline): mixed
    {
        $this->authorize('update', $cartline);

        $validatedData = $request->validated();

        try {
            $result = app(UpdateCartLineAction::class)
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
        dd($cartline->cart->session_id = $sessionId);

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
