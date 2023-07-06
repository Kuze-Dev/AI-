<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CheckoutRequest;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
}
