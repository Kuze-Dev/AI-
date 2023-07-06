<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use Domain\Cart\Actions\CartStoreAction;
use Domain\Cart\DataTransferObjects\CartStoreData;
use Domain\Cart\Enums\CartActionResult;
use Domain\Cart\Requests\CartStoreRequest;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[
    Prefix('carts'),
]
class CartController extends Controller
{
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
}
