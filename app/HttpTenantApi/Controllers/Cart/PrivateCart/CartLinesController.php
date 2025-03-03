<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart\PrivateCart;

use Illuminate\Container\Attributes\CurrentUser;
use App\Http\Controllers\Controller;
use Domain\Cart\Actions\CreateCartAction;
use Domain\Cart\Actions\CreateCartLineAction;
use Domain\Cart\Actions\DestroyCartLineAction;
use Domain\Cart\Actions\UpdateCartLineAction;
use Domain\Cart\DataTransferObjects\CreateCartData;
use Domain\Cart\DataTransferObjects\UpdateCartLineData;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CreateCartLineRequest;
use Domain\Cart\Requests\UpdateCartLineRequest;
use Domain\Customer\Models\Customer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\Support\File;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Resource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[
    Resource('carts/cartlines', apiResource: true, except: ['show', 'index'], names: 'carts.cartlines'),
    Middleware(['auth:sanctum'])
]
class CartLinesController extends Controller
{
    public function store(CreateCartLineRequest $request,#[CurrentUser('sanctum')] Customer $customer): mixed
    {
        $validatedData = $request->validated();


        try {
            $dbResult = DB::transaction(function () use ($validatedData, $customer) {
                $cart = app(CreateCartAction::class)->execute($customer);

                if (! $cart instanceof Cart) {
                    return response()->json([
                        'message' => 'Invalid action',
                    ], 400);
                }

                app(CreateCartLineAction::class)
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
            $maxFileSize = File::getHumanReadableSize(config()->integer('media-library.max_file_size'));
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

        $result = app(DestroyCartLineAction::class)
            ->execute($cartline);

        if (! $result) {
            return response()->json([
                'message' => 'Invalid action',
            ], 400);
        }

        return response()->noContent();
    }
}
