<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PrivateCart;

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
use Domain\Cart\Models\Cart;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Spatie\MediaLibrary\Support\File;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

#[
    Resource('carts/cartlines', apiResource: true, except: ['show', 'index']),
    Middleware(['auth:sanctum'])
]
class CartLinesController extends Controller
{
    public function __construct(
        private readonly CreateCartAction $createCartAction,
        private readonly CreateCartLineAction $createCartLineAction,
        private readonly UpdateCartLineAction $updateCartLineAction,
        private readonly DestroyCartLineAction $destroyCartLineAction
    ) {
    }

    public function store(CreateCartLineRequest $request): mixed
    {
        $validatedData = $request->validated();

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        try {
            $dbResult = DB::transaction(function () use ($validatedData, $customer) {
                $cart = $this->createCartAction->execute($customer);

                if ( ! $cart instanceof Cart) {
                    return response()->json([
                        'message' => 'Invalid action',
                    ], 400);
                }

                $this->createCartLineAction
                    ->execute($cart, CreateCartData::fromArray($validatedData));

                return [
                    'message' => 'Successfully Added to Cart',
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
        $this->authorize('update', $cartline);

        $validatedData = $request->validated();

        try {
            $dbResult = DB::transaction(function () use ($validatedData, $cartline) {
                $result = $this->updateCartLineAction
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

    public function destroy(CartLine $cartline): mixed
    {
        $this->authorize('delete', $cartline);

        $result = $this->destroyCartLineAction
            ->execute($cartline);

        if ( ! $result) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()->noContent();
    }
}
