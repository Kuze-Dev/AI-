<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\HttpTenantApi\Resources\CartLineResource;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CheckoutRequest;
use Domain\Product\Models\ProductVariant;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\RouteAttributes\Attributes\Get;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

#[
    Prefix('checkouts'),
    Middleware(['auth:sanctum'])
]
class CheckoutController extends Controller
{
    #[Post('/', name: 'checkouts')]
    public function checkout(CheckoutRequest $request)
    {
        $validatedData = $request->validated();

        $payload = CheckoutData::fromArray($validatedData);

        $cartLinesForCheckout = CartLine::select(['cart_lines.id', 'cart_lines.purchasable_id'])
            ->join('products', 'cart_lines.purchasable_id', '=', 'products.id')
            ->whereNull('checked_out_at')
            ->where('products.stock', '>', DB::raw('cart_lines.quantity'))
            ->whereIn('cart_lines.id', $payload->cart_line_ids)
            ->get();

        if ($cartLinesForCheckout->count() !== count($payload->cart_line_ids)) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        $cartLineIds = $cartLinesForCheckout->pluck('id');
        $purchasablesForCheckout = $cartLinesForCheckout->pluck('purchasable_id');

        $checkoutReference = Str::upper(Str::random(12));

        CartLine::whereIn('id', $cartLineIds)
            ->update([
                'checkout_reference' => $checkoutReference,
                'checkout_expiration' => now()->addMinutes(20)
            ]);

        return response()
            ->json([
                'message' => 'Success',
                'purchasables_for_checkout' => $purchasablesForCheckout,
                'reference' => $checkoutReference
            ]);
    }

    #[Get('/', 'checkouts')]
    public function checkoutItems(Request $request)
    {
        $customerId = auth()->user()->id;

        $reference = $request->input('reference');

        if (!$reference) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => 'Invalid reference.'
            ], 400);
        }

        $purchasableIds = json_decode($request->input('purchasable_ids', '[]'), true);

        if (!is_array($purchasableIds)) {
            $purchasableIds = [];
        }

        $cartLineQuery = CartLine::with(["purchasable", 'media'])->whereHas('cart', function ($query) use ($customerId) {
            $query->whereCustomerId($customerId);
        })->whereIn('purchasable_id', $purchasableIds)
            ->whereCheckoutReference($reference)
            ->where('checkout_expiration', '>', now());

        $cartLines = $cartLineQuery->get();

        if (count($cartLines) <= 0) {
            return response()->json([
                'error' => 'Bad Request',
                'message' => 'Invalid cart line IDs.'
            ], 400);
        }

        return CartLineResource::collection(
            QueryBuilder::for(
                $cartLineQuery
            )->jsonPaginate()
        );
    }
}
