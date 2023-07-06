<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use Domain\Cart\Actions\CartStoreAction;
use Domain\Cart\DataTransferObjects\CartStoreData;
use Domain\Cart\Enums\CartActionResult;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartStoreRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

#[
    Prefix('carts'),
]
class CartController extends Controller
{
    #[Get('/{cartId}', name: 'cart.show.{cartId}')]
    public function show(int $cartId, Request $request)
    {
        $perPage = $request->query('per_page', 10);

        try {
            Cart::findOrFail($cartId);

            $model = QueryBuilder::for(
                CartLine::with(["purchasable.media", 'variant'])
                    ->where("cart_id", $cartId)
                    ->whereNull("checked_out_at")
            )->jsonPaginate($perPage);

            $model->getCollection()->transform(function ($item) {
                $item->purchasable->image_url = $item->purchasable->getFirstMediaUrl('image');
                $item->purchasable->unsetRelation('media');

                $notesCollection = $item->getMedia('cart_line_notes');
                $item->purchasable->notes_image_url = $notesCollection->isEmpty() ? null : $notesCollection->first()->getUrl('preview');

                return $item;
            });

            return $model;
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
}
