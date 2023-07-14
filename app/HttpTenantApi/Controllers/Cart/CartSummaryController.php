<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartSummaryRequest;
use Domain\Customer\Models\Customer;
use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Facades\Taxation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;

#[
    Middleware(['auth:sanctum'])
]
class CartSummaryController extends Controller
{
    #[Get('carts/count', name: 'carts.count')]
    public function count()
    {
        $cartLinesCount = CartLine::query()
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->count();

        return response()->json(['cartCount' => $cartLinesCount], 200);
    }

    #[Get('carts/summary', name: 'carts.summary')]
    public function summary(CartSummaryRequest $request)
    {
        $validated = $request->validated();

        $cartLineIdArray = explode(',', $validated['cart_line_ids']);
        $cartLineIds = array_map('intval', $cartLineIdArray);

        $stateId = $validated['state_id'] ?? null;
        $countryId = $validated['country_id'];

        $cartLines = CartLine::query()
            ->with('purchasable')
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->whereIn('id', $cartLineIds)
            ->get();


        $subTotal =  $cartLines->reduce(function ($carry, $cartLine) {
            $purchasable = $cartLine->purchasable;

            return $carry + ($purchasable->selling_price * $cartLine->quantity);
        }, 0);

        $taxZone = Taxation::getTaxZone($countryId, $stateId);
        $taxDisplay = $taxZone->price_display;
        $taxPercentage = (float) $taxZone->percentage;
        $taxTotal = round($subTotal * $taxPercentage / 100, 2);

        //for now, but the shipping fee and discount will be added
        $grandTotal = $subTotal + $taxTotal;

        return response()->json([
            'tax_inclusive_sub_total' => $subTotal + $taxTotal,
            'sub_total' => $subTotal,
            'tax_display' => $taxDisplay,
            'tax_percentage' => $taxPercentage,
            'tax_total' => $taxTotal,
            'grand_total' => $grandTotal
        ], 200);
    }
}
