<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use Domain\Cart\Helpers\CartLineHelper;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartSummaryRequest;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Taxation\Facades\Taxation;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;

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

        $stateId = (int) $validated['state_id'] ?? null;
        $countryId = (int) $validated['country_id'];

        $discount = null;
        $discountCode = $validated['discount_code'] ?? null;

        if (!is_null($discountCode)) {
            $discount = Discount::whereCode($discountCode)
                ->whereStatus(DiscountStatus::ACTIVE)
                ->where(function ($query) {
                    $query->where('max_uses', '>', 0)
                        ->orWhereNull('max_uses');
                })
                ->where(function ($query) {
                    $query->where('valid_end_at', '>=', now())
                        ->orWhereNull('valid_end_at');
                })->first();
        }

        $cartLines = CartLine::query()
            ->with('purchasable')
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->whereIn('id', $cartLineIds)
            ->get();


        $summaryData = app(CartLineHelper::class)
            ->summary($cartLines, $countryId, $stateId, $discount);

        return response()->json([
            'tax_inclusive_sub_total' => $summaryData->subTotal + $summaryData->taxTotal,
            'sub_total' => $summaryData->subTotal,
            'tax_display' => $summaryData->taxDisplay,
            'tax_percentage' => $summaryData->taxPercentage,
            'tax_total' => $summaryData->taxTotal,
            'grand_total' => $summaryData->grandTotal,
            'discount_total' => $discountCode ? $summaryData->discountTotal : "",
            'discount_message' => $discountCode ? $summaryData->discountMessage : "",
        ], 200);
    }
}
