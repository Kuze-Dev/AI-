<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Cart\Helpers\CartLineHelper;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartSummaryRequest;
use Domain\Customer\Models\Customer;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Domain\Shipment\Actions\USPS\GetUSPSRateAction;
use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    #[Get('carts/summary/{country}/{shippingMethod}/{shippingAddress}/{stateId?}/{discountCode?}', name: 'carts.summary')]
    public function summary(
        CartSummaryRequest $request,
        Country $country,
        ShippingMethod $shippingMethod,
        Address $shippingAddress,
        ?string $stateId,
        ?string $discountCode
    ) {
        $validated = $request->validated();

        $cartLineIdArray = explode(',', $validated['cart_line_ids']);
        $cartLineIds = array_map('intval', $cartLineIdArray);

        $customer = auth()->user()->load('addresses.state.country');
        $state = $stateId ? app(State::class)->resolveRouteBinding($stateId) : null;

        $discount = $discountCode ? app(Discount::class)->resolveRouteBinding($discountCode) : null;

        $validationResponse = $this->validateAddress($customer, $country, $state);

        if ($validationResponse) {
            return $validationResponse;
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
            ->summary($cartLines, $country->id, (int) $stateId, $discount);

        $parcelData =  new ParcelData(
            pounds: '10',
            ounces: '0',
            width: '10',
            height: '10',
            length: '10',
            zip_origin: $shippingMethod->ship_from_address['zip5'],
            parcel_value: '200',
        );

        try {
            $shippingFee = app(GetUSPSRateAction::class)
                ->execute($customer, $parcelData, $shippingMethod, $shippingAddress);
        } catch (USPSServiceNotFoundException) {
            return response()->json([
                "service_id" => "Service id is required",
            ], 404);
        }

        return response()->json([
            'tax_inclusive_sub_total' => $summaryData->subTotal + $summaryData->taxTotal,
            'sub_total' => $summaryData->subTotal,
            'tax_display' => $summaryData->taxDisplay,
            'tax_percentage' => $summaryData->taxPercentage,
            'tax_total' => $summaryData->taxTotal,
            'grand_total' => $summaryData->grandTotal,
            'discount_total' => $discountCode ? $summaryData->discountTotal : '',
            'discount_message' => $discountCode ? $summaryData->discountMessage : '',
            "shipping_fee" => $shippingFee
        ], 200);
    }

    private function validateAddress(Customer $customer, Country $country, ?State $state = null)
    {
        if (!$customer->addresses->pluck('state.country.id')->contains($country->id)) {
            return response()->json([
                "country" => "Invalid country",
            ], 404);
        }

        if ($state && !$customer->addresses->pluck('state_id')->contains($state->id)) {
            return response()->json([
                "state" => "Invalid state",
            ], 404);
        }
    }
}
