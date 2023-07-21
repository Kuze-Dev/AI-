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

    #[Get('carts/summary/{country}/{shippingMethodId?}/{shippingAddressId?}/{stateId?}/{discountCode?}', name: 'carts.summary')]
    public function summary(
        CartSummaryRequest $request,
        Country $country,
        ?string $shippingMethodId,
        ?string $shippingAddressId,
        ?string $stateId,
        ?string $discountCode
    ) {
        $validated = $request->validated();

        $cartLineIdArray = explode(',', $validated['cart_line_ids']);
        $cartLineIds = array_map('intval', $cartLineIdArray);

        $customer = auth()->user()->load('addresses.state.country');

        $state = $this->resolveSummaryRouteKey(State::class, $stateId);

        $shippingMethod = $this->resolveSummaryRouteKey(ShippingMethod::class, $shippingMethodId);
        $shippingAddress = $this->resolveSummaryRouteKey(Address::class, $shippingAddressId);

        $discount = $this->resolveSummaryRouteKey(Discount::class, $discountCode);

        $validationResponse = $this->validateAddress($customer, $country, $shippingAddress, $state);

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

        $taxDetails = app(CartLineHelper::class)->getTax($country->id);

        $discountDetails = app(CartLineHelper::class)->getDiscount($discount, 167.79);

        $subTotalDetails = app(CartLineHelper::class)->getSubTotal($cartLines);

        return $subTotalDetails;

        //     $summaryData = app(CartLineHelper::class)
        //     ->summary($cartLines, $country->id, (int) $stateId, $discount);


        // return response()->json([
        //     'tax_inclusive_sub_total' => $summaryData->subTotal + $summaryData->taxTotal,
        //     'sub_total' => $summaryData->subTotal,
        //     'tax_display' => $summaryData->taxDisplay,
        //     'tax_percentage' => $summaryData->taxPercentage,
        //     'tax_total' => $summaryData->taxTotal,
        //     'grand_total' => $summaryData->grandTotal,
        //     'discount_total' => $discountCode ? $summaryData->discountTotal : '',
        //     'discount_message' => $discountCode ? $summaryData->discountMessage : '',
        //     "shipping_fee" => $shippingFee
        // ], 200);
    }

    private function resolveSummaryRouteKey(string $class, ?string $routeKey)
    {
        return $routeKey ? app($class)->resolveRouteBinding($routeKey) : null;
    }

    private function validateAddress(
        Customer $customer,
        Country $country,
        ?Address $shippingAddress,
        ?State $state = null
    ) {
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

        if ($shippingAddress && !$customer->addresses->contains('id', $shippingAddress->id)) {
            return response()->json([
                "shipping_address" => "Invalid shipping address",
            ], 404);
        }
    }
}
