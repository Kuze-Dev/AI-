<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use Domain\Cart\DataTransferObjects\CartSummaryShippingData;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\Helpers\CartLineHelper;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartSummaryRequest;
use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
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

        $cartLineIds = explode(',', $validated['cart_line_ids']);

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        $cartLines = CartLine::query()
            ->with('purchasable')
            ->whereHas('cart', function ($query) {
                $query->whereBelongsTo(auth()->user());
            })
            ->whereNull('checked_out_at')
            ->whereIn((new CartLine())->getRouteKeyName(), $cartLineIds)
            ->get();

        $country = $request->getCountry();
        $state = $request->getState();
        $discount = $request->getDiscount();

        try {
            $summary = app(CartLineHelper::class)->getSummary(
                $cartLines,
                new CartSummaryTaxData($country?->id, $state?->id),
                new CartSummaryShippingData($customer, $request->getShippingAddress(), $request->getShippingMethod()),
                $discount,
            );
        } catch (USPSServiceNotFoundException) {
            return response()->json([
                'service_id' => 'Shipping method service id is required',
            ], 404);
        }

        return response()->json([
            'tax' => [
                'inclusive_sub_total' => $summary->taxTotal ? round($summary->subTotal + $summary->taxTotal, 2) : null,
                'display' => $summary->taxTotal ? $summary->taxDisplay : null,
                'percentage' => $summary->taxPercentage ? round($summary->taxPercentage, 2) : 0,
                'amount' => $summary->taxTotal ? round($summary->taxTotal, 2) : 0,
            ],
            'discount' => [
                'status' => round($summary->discountTotal, 2) ? 'valid' : 'invalid',
                'amount' => $discount ? round($summary->discountTotal, 2) : 0,
                // "message"
            ],
            'sub_total' => round($summary->subTotal, 2),
            'shipping_fee' => round($summary->shippingTotal, 2),
            'total' => round($summary->grandTotal, 2),
        ], 200);
    }
}
