<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use App\Http\Controllers\Controller;
use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Cart\DataTransferObjects\CartSummaryShippingData;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\Helpers\CartLineHelper;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartSummaryRequest;
use Domain\Customer\Models\Customer;
use Domain\Discount\Models\Discount;
use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
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

        $cartLineIds = explode(',', $validated['cart_line_ids']);

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = auth()->user();

        $customer->load('addresses.state.country');

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
            ->whereIn((new CartLine())->getRouteKeyName(), $cartLineIds)
            ->get();

        try {

            $summary = app(CartLineHelper::class)->getSummary(
                $cartLines,
                new CartSummaryTaxData($country->id, $state->id ?? null),
                new CartSummaryShippingData($customer, $shippingAddress, $shippingMethod),
                $discount,
            );
        } catch (USPSServiceNotFoundException) {
            return response()->json([
                'service_id' => 'Shipping method service id is required',
            ], 404);
        }

        return response()->json([
            'tax_inclusive_sub_total' => round($summary->subTotal + $summary->taxTotal, 2),
            'sub_total' => round($summary->subTotal, 2),
            'tax_display' => $summary->taxDisplay,
            'tax_percentage' => round($summary->taxPercentage, 2),
            'tax_total' => round($summary->taxTotal, 2),
            'grand_total' => round($summary->grandTotal, 2),
            'discount_total' => $discountCode ? round($summary->discountTotal, 2) : '',
            // 'discount_message' => $discountCode ? $summary->discountMessage : '',
            'shipping_fee' => round($summary->shippingTotal, 2),
        ], 200);
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
        if ( ! $customer->addresses->pluck('state.country.id')->contains($country->id)) {
            return response()->json([
                'country' => 'Invalid country',
            ], 404);
        }

        if ($state && ! $customer->addresses->pluck('state_id')->contains($state->id)) {
            return response()->json([
                'state' => 'Invalid state',
            ], 404);
        }

        if ($shippingAddress && ! $customer->addresses->contains('id', $shippingAddress->id)) {
            return response()->json([
                'shipping_address' => 'Invalid shipping address',
            ], 404);
        }
    }
}
