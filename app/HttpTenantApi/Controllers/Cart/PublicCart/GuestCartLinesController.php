<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PublicCart;

use App\Features\ECommerce\AllowGuestOrder;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Spatie\MediaLibrary\Support\File;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\RouteAttributes\Attributes\Middleware;

#[
    Resource('guest/carts/cartlines', apiResource: true, except: ['show', 'index'], names: 'guest.carts.cartlines'),
    Middleware(['feature.tenant:' . AllowGuestOrder::class])
]
class GuestCartLinesController extends Controller
{
    public function store(CreateCartLineRequest $request): mixed
    {
        $validatedData = $request->validated();
        $sessionId = $request->bearerToken() ?? null;

        try {
            $dbResult = DB::transaction(function () use ($validatedData, $sessionId) {
                $cart = app(GuestCreateCartAction::class)->execute($sessionId);

                if ( ! $cart instanceof Cart) {
                    return response()->json([
                        'message' => 'Invalid action',
                    ], 400);
                }

                app(CreateCartLineAction::class)
                    ->execute($cart, CreateCartData::fromArray($validatedData));

                return [
                    'message' => 'Successfully Added to Cart',
                    'session_id' => $cart->session_id,
                ];
            });

            return response()->json($dbResult);
        } catch (BadRequestHttpException $e) {
            return response()->json([
                'message' => 'Invalid action',
                'error' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            Log::error([
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    public function update(UpdateCartLineRequest $request, CartLine $cartline): mixed
    {
        $cartline->load('cart');
        $sessionId = $request->bearerToken();

        $allowed = app(AuthorizeGuestCart::class)->execute($cartline, $sessionId);

        if ( ! $allowed) {
            abort(403);
        }

        $validatedData = $request->validated();

        try {
            $dbResult = DB::transaction(function () use ($validatedData, $cartline) {
                $result = app(UpdateCartLineAction::class)
                    ->execute($cartline, UpdateCartLineData::fromArray($validatedData));

                if ($result instanceof CartLine) {
                    return [
                        'message' => 'Cart updated successfully',
                    ];
                }
            });

            return response()->json($dbResult);
        } catch (Exception $e) {
            $maxFileSize = File::getHumanReadableSize(config('media-library.max_file_size'));
            if ($e instanceof FileIsTooBig) {
                return response()->json([
                    'message' => "File is too big , please upload file less than $maxFileSize",
                ], 400);
            }

            Log::error([
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }
    }

    public function destroy(Request $request, CartLine $cartline): mixed
    {
        $cartline->load('cart');
        $sessionId = $request->bearerToken();

        $allowed = app(AuthorizeGuestCart::class)->execute($cartline, $sessionId);

        if ( ! $allowed) {
            abort(403);
        }

        $result = app(DestroyCartLineAction::class)->execute($cartline);

        if ( ! $result) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()->noContent();
    }
}
