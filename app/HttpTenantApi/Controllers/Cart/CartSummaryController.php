<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use Domain\Address\Models\Address;
use Domain\Cart\Helpers\CartLineHelper;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartSummaryRequest;
use Domain\Customer\Models\Customer;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Shipment\Actions\USPS\GetUSPSRateAction;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\ShippingMethod\Models\ShippingMethod;
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

        // $cartLineIdArray = explode(',', $validated['cart_line_ids']);
        // $cartLineIds = array_map('intval', $cartLineIdArray);

        // $stateId = $validated['state_id'] ? (int) $validated['state_id'] : null;
        // $countryId = (int) $validated['country_id'];

        // $discount = null;
        // $discountCode = $validated['discount_code'] ?? null;

        $shippingMethodSlug = $validated['shipping_method'];
        $shippingAddressId = $validated['shipping_address_id'];

        // // return $shippingMethod;

        // if (!is_null($discountCode)) {
        //     $discount = Discount::whereCode($discountCode)
        //         ->whereStatus(DiscountStatus::ACTIVE)
        //         ->where(function ($query) {
        //             $query->where('max_uses', '>', 0)
        //                 ->orWhereNull('max_uses');
        //         })
        //         ->where(function ($query) {
        //             $query->where('valid_end_at', '>=', now())
        //                 ->orWhereNull('valid_end_at');
        //         })->first();
        // }

        // $cartLines = CartLine::query()
        //     ->with('purchasable')
        //     ->whereHas('cart', function ($query) {
        //         $query->whereBelongsTo(auth()->user());
        //     })
        //     ->whereNull('checked_out_at')
        //     ->whereIn('id', $cartLineIds)
        //     ->get();

        // $summaryData = app(CartLineHelper::class)
        //     ->summary($cartLines, $countryId, $stateId, $discount);

        $shippingAddress = Address::find($shippingAddressId);

        $shippingMethod = ShippingMethod::whereSlug($shippingMethodSlug)->first();

        $parcelData =  new ParcelData(
            pounds: '10',
            ounces: '0',
            width: '10',
            height: '10',
            length: '10',
            zip_origin: $shippingMethod->ship_from_address['zip5'],
            parcel_value: '200',
        );

        $customer = Customer::find(auth()->user()?->id);

        $shippingFee = app(GetUSPSRateAction::class)
            ->execute($customer, $parcelData, $shippingMethod, $shippingAddress);

        return $shippingFee;

        // return response()->json([
        //     'tax_inclusive_sub_total' => $summaryData->subTotal + $summaryData->taxTotal,
        //     'sub_total' => $summaryData->subTotal,
        //     'tax_display' => $summaryData->taxDisplay,
        //     'tax_percentage' => $summaryData->taxPercentage,
        //     'tax_total' => $summaryData->taxTotal,
        //     'grand_total' => $summaryData->grandTotal,
        //     'discount_total' => $discountCode ? $summaryData->discountTotal : '',
        //     'discount_message' => $discountCode ? $summaryData->discountMessage : '',
        // ], 200);
    }
}
